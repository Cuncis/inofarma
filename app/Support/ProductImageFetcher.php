<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Downloads product photos for a bulk import.
 *
 * Downloads are queued while rows are saved, then fetched several at a time at
 * the end. Fetching ~300 images one by one took longer than the request's time
 * limit and killed the import halfway through.
 */
class ProductImageFetcher
{
    private const CONCURRENCY = 10;

    /** @var list<array{product: Product, url: string}> */
    private array $pending = [];

    /**
     * @param  list<string>  $urls  the first one becomes the primary image
     */
    public function queue(Product $product, array $urls): void
    {
        foreach ($urls as $url) {
            $this->pending[] = ['product' => $product, 'url' => $url];
        }
    }

    /**
     * @param  array{imagesFailed: list<string>}  $summary
     */
    public function fetch(array &$summary): void
    {
        foreach (array_chunk($this->pending, self::CONCURRENCY, preserve_keys: true) as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $key => $pending) {
                    $pool->as((string) $key)
                        ->withHeaders(['User-Agent' => 'Inofarma-ProductImport/1.0 (internal catalogue import)'])
                        ->timeout(20)
                        ->get($pending['url']);
                }
            });

            foreach ($chunk as $key => $pending) {
                try {
                    $response = $responses[(string) $key] ?? null;

                    if (! $response instanceof Response || ! $response->successful()) {
                        throw new \RuntimeException("Gagal mengunduh gambar: {$pending['url']}");
                    }

                    $this->store($pending['product'], $pending['url'], $response);
                } catch (\Throwable) {
                    $summary['imagesFailed'][] = $pending['product']->sku;
                }
            }
        }

        $summary['imagesFailed'] = array_values(array_unique($summary['imagesFailed']));
        $this->pending = [];
    }

    private function store(Product $product, string $url, Response $response): void
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $tempPath = tempnam(sys_get_temp_dir(), 'csvimg');
        file_put_contents($tempPath, $response->body());

        try {
            $file = new UploadedFile(
                $tempPath,
                "import.{$extension}",
                $response->header('Content-Type') ?: 'image/jpeg',
                null,
                true,
            );

            $upload = ProductImageUploader::store($file, $product->id);
            $position = $product->images()->count() + 1;

            $product->images()->create([
                'path' => $upload['path'],
                'position' => $position,
                'is_primary' => $position === 1,
            ]);
        } finally {
            @unlink($tempPath);
        }
    }
}

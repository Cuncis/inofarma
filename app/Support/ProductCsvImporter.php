<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Bulk-creates or updates products from a Shopify product-export CSV.
 *
 * The CSV's `Variant SKU` becomes `products.sku` directly (not a `PRD-` {@see
 * CodeSequence}), and `Handle` becomes `slug` directly, so importing the same
 * file twice updates the same rows instead of duplicating them. Only product
 * data is touched here — no stock, batch, or branch assignment is created;
 * that stays a separate, deliberate admin action.
 *
 * `Golongan Obat: BLUE` maps to `drug_class = 'bebas terbatas'`, which by law
 * must show a P1–P6 warning ({@see Product::getNeedsWarningLabelAttribute()}).
 * The CSV carries no warning text, so those rows import as `nonaktif` rather
 * than silently going live without one — an admin has to add the warning and
 * activate the product by hand.
 */
class ProductCsvImporter
{
    /** `Golongan Obat` (CSV) → `products.drug_class`. Unmapped values fall back to 'non-obat'. */
    private const DRUG_CLASS_MAP = [
        'GREEN' => 'bebas',
        'BLUE' => 'bebas terbatas',
        'KUASI' => 'bebas',
        'OBAT TRADISIONAL' => 'bebas',
        'FITOFARMAKA' => 'bebas',
        'OHT' => 'bebas',
        'SUPLEMEN' => 'non-obat',
        'PKRT' => 'non-obat',
        'ALAT KESEHATAN - NON ELEKTROMEDIK NON STERIL' => 'non-obat',
    ];

    /**
     * Keyword found anywhere in the packaging line → `products.unit`. Checked
     * in order, so "Dus, 1 Botol Plastik @ 60 Ml" matches Botol (the sellable
     * unit) before it matches Dus (the outer box).
     */
    private const UNIT_KEYWORDS = [
        'Botol' => 'Botol',
        'Strip' => 'Strip',
        'Tablet' => 'Tablet',
        'Kaplet' => 'Tablet',
        'Kapsul' => 'Tablet',
        'Box' => 'Box',
        'Dus' => 'Box',
    ];

    /** @var array<string, int> */
    private array $categoryCache = [];

    /**
     * @return array{created: int, updated: int, warnedInactive: int, imagesFailed: list<string>, failed: list<array{row: int, message: string}>}
     */
    public function import(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, escape: '\\');
        $index = array_flip($header);

        $summary = ['created' => 0, 'updated' => 0, 'warnedInactive' => 0, 'imagesFailed' => [], 'failed' => []];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $rowNumber++;

            try {
                $this->importRow($row, $index, $summary);
            } catch (\Throwable $e) {
                $summary['failed'][] = ['row' => $rowNumber, 'message' => $e->getMessage()];
            }
        }

        fclose($handle);

        return $summary;
    }

    /**
     * @param  list<string>  $row
     * @param  array<string, int>  $index
     * @param  array{created: int, updated: int, warnedInactive: int, imagesFailed: list<string>, failed: array}  $summary
     */
    private function importRow(array $row, array $index, array &$summary): void
    {
        $sku = trim($row[$index['Variant SKU']] ?? '');

        if ($sku === '') {
            throw new \RuntimeException('Variant SKU kosong.');
        }

        $body = $this->parseBody($row[$index['Body (HTML)']] ?? '');
        $tags = $this->parseTags($row[$index['Tags']] ?? '');
        $drugClass = self::DRUG_CLASS_MAP[$body['golongan']] ?? 'non-obat';
        $needsWarning = $drugClass === 'bebas terbatas';

        $product = Product::withTrashed()->where('sku', $sku)->first();
        $isNew = $product === null;
        $product ??= new Product(['sku' => $sku]);

        if ($product->trashed()) {
            $product->restore();
        }

        $title = trim($row[$index['Title']] ?? '');

        $product->fill([
            'name' => $title,
            'slug' => trim($row[$index['Handle']] ?? '') ?: Str::slug($title !== '' ? $title : $sku),
            'category_id' => $this->categoryId($tags['category']),
            'unit' => $this->guessUnit($body['kemasan']),
            'status' => $needsWarning ? 'nonaktif' : 'aktif',
            'price' => (int) round((float) ($row[$index['Variant Price']] ?? 0)),
            'old_price' => $this->nullableInt($row[$index['Variant Compare At Price']] ?? null),
            'requires_prescription' => false,
            'blurb' => $body['deskripsi'] !== '' ? Str::limit($body['deskripsi'], 500, '') : null,
            'description' => $this->buildDescription($body) ?: null,
            'drug_class' => $drugClass,
            'nie_bpom' => $body['nie'] !== '' ? $body['nie'] : null,
            'composition' => $body['komposisi'] !== '' ? Str::limit($body['komposisi'], 255, '') : null,
            'indication' => $this->guessIndication($body['deskripsi']),
            'max_qty_per_order' => $tags['maxQty'],
            'storage' => 'suhu ruang',
            'weight_grams' => $this->nullableInt($row[$index['Variant Grams']] ?? null) ?? 0,
        ]);

        $product->save();

        $summary[$isNew ? 'created' : 'updated']++;

        if ($needsWarning) {
            $summary['warnedInactive']++;
        }

        $imageUrl = trim($row[$index['Image Src']] ?? '');

        if ($imageUrl !== '' && ! $product->images()->exists()) {
            try {
                $this->attachImage($product, $imageUrl);
            } catch (\Throwable) {
                $summary['imagesFailed'][] = $sku;
            }
        }
    }

    /**
     * `Body (HTML)` is always `<p>kemasan</p><p>Label: value</p>…` in this
     * export — every one of the 316 rows in the reference file follows this
     * exact shape, so a positional-ish label scan is enough; anything that
     * doesn't match a known label (e.g. `Exp Date`, batch-level and out of
     * scope for a product-only import) is ignored rather than guessed at.
     *
     * @return array{kemasan: string, deskripsi: string, komposisi: string, golongan: string, bentukSediaan: string, nie: string}
     */
    private function parseBody(string $html): array
    {
        preg_match_all('/<p>(.*?)<\/p>/s', $html, $matches);

        $paragraphs = array_map(
            fn (string $p) => trim(html_entity_decode(strip_tags($p))),
            $matches[1],
        );

        $fields = [
            'kemasan' => $paragraphs[0] ?? '',
            'deskripsi' => '',
            'komposisi' => '',
            'golongan' => '',
            'bentukSediaan' => '',
            'nie' => '',
        ];

        foreach (array_slice($paragraphs, 1) as $paragraph) {
            if (! preg_match('/^([^:]+):\s*(.*)$/s', $paragraph, $m)) {
                continue;
            }

            match (trim($m[1])) {
                'Deskripsi' => $fields['deskripsi'] = trim($m[2]),
                'Komposisi' => $fields['komposisi'] = trim($m[2]),
                'Golongan Obat' => $fields['golongan'] = strtoupper(trim($m[2])),
                'Bentuk Sediaan' => $fields['bentukSediaan'] = trim($m[2]),
                'NIE' => $fields['nie'] = trim($m[2]),
                default => null,
            };
        }

        return $fields;
    }

    /**
     * @param  array{kemasan: string, deskripsi: string, bentukSediaan: string}  $body
     */
    private function buildDescription(array $body): string
    {
        return collect([
            $body['kemasan'] !== '' ? "Kemasan: {$body['kemasan']}" : null,
            $body['deskripsi'] !== '' ? $body['deskripsi'] : null,
            $body['bentukSediaan'] !== '' ? "Bentuk Sediaan: {$body['bentukSediaan']}" : null,
        ])->filter()->implode("\n\n");
    }

    /** Best-effort only — null when the sentence doesn't match a recognizable pattern, never fabricated. */
    private function guessIndication(string $deskripsi): ?string
    {
        if (preg_match('/yang (?:diindikasikan untuk|berkhasiat untuk|berfungsi untuk) (.+)/i', $deskripsi, $m)) {
            return Str::limit(rtrim(trim($m[1]), '.'), 1000, '');
        }

        return null;
    }

    private function guessUnit(string $kemasan): string
    {
        foreach (self::UNIT_KEYWORDS as $keyword => $unit) {
            if (Str::contains($kemasan, $keyword, ignoreCase: true)) {
                return $unit;
            }
        }

        return 'Pcs';
    }

    /**
     * `Tags` is always exactly one category name plus one `mqty:N` tag in this
     * export. `mqty:0` means "no limit", which the app expresses as null.
     *
     * @return array{category: string, maxQty: ?int}
     */
    private function parseTags(string $raw): array
    {
        $tags = array_filter(array_map('trim', explode(',', $raw)));
        $category = 'Kesehatan';
        $maxQty = null;

        foreach ($tags as $tag) {
            if (preg_match('/^mqty:(\d+)$/i', $tag, $m)) {
                $maxQty = ((int) $m[1]) > 0 ? (int) $m[1] : null;
            } else {
                $category = $tag;
            }
        }

        return ['category' => $category, 'maxQty' => $maxQty];
    }

    private function categoryId(string $name): int
    {
        if (isset($this->categoryCache[$name])) {
            return $this->categoryCache[$name];
        }

        $category = Category::withTrashed()->where('name', $name)->first();

        if ($category && $category->trashed()) {
            $category->restore();
        }

        $category ??= Category::create([
            'name' => $name,
            'slug' => Slug::unique(Category::withTrashed(), $name),
            'status' => 'aktif',
            'position' => (int) Category::max('position') + 1,
            'image_path' => '/media/images/small/img-'.(Category::count() % 12 + 1).'.jpg',
        ]);

        return $this->categoryCache[$name] = $category->id;
    }

    private function nullableInt(?string $value): ?int
    {
        $value = trim((string) $value);

        return $value === '' || (float) $value <= 0 ? null : (int) round((float) $value);
    }

    private function attachImage(Product $product, string $url): void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Inofarma-ProductImport/1.0 (internal catalogue import)',
        ])->timeout(20)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Gagal mengunduh gambar: {$url}");
        }

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

            $product->images()->create([
                'path' => $upload['path'],
                'position' => 1,
                'is_primary' => true,
            ]);
        } finally {
            @unlink($tempPath);
        }
    }
}

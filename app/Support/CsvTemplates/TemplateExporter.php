<?php

namespace App\Support\CsvTemplates;

use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Writes `produk.csv` / `stok.csv` downloads: the live data, or an empty
 * sample with two example rows. The columns are exactly what the importers
 * read, so an export can be edited and imported straight back.
 */
class TemplateExporter
{
    public function products(): StreamedResponse
    {
        return $this->download('produk-'.now()->format('Ymd-His').'.csv', ProductTemplate::COLUMNS, function ($out) {
            Product::query()
                ->with(['category', 'supplier', 'images'])
                ->orderBy('sku')
                ->chunk(500, function ($products) use ($out) {
                    foreach ($products as $product) {
                        $this->writeRow($out, ProductTemplate::COLUMNS, $this->productRow($product));
                    }
                });
        });
    }

    public function stock(): StreamedResponse
    {
        return $this->download('stok-'.now()->format('Ymd-His').'.csv', StockTemplate::COLUMNS, function ($out) {
            $settings = BranchStock::query()->get()->keyBy(fn (BranchStock $row) => "{$row->branch_id}-{$row->product_id}");

            InventoryBatch::query()
                ->with(['branch', 'product'])
                ->where('quantity', '>', 0)
                ->orderBy('branch_id')
                ->orderBy('product_id')
                ->orderBy('expires_at')
                ->chunk(500, function ($batches) use ($out, $settings) {
                    foreach ($batches as $batch) {
                        if ($batch->branch === null || $batch->product === null) {
                            continue;
                        }

                        $branchStock = $settings->get("{$batch->branch_id}-{$batch->product_id}");

                        $this->writeRow($out, StockTemplate::COLUMNS, [
                            'sku' => $batch->product->sku,
                            'branch_code' => $batch->branch->code,
                            'batch_number' => $batch->batch_number,
                            'expires_at' => $batch->expires_at->toDateString(),
                            'quantity' => $batch->quantity,
                            'batch_cost_price' => $batch->cost_price,
                            'received_at' => $batch->received_at?->toDateString(),
                            'reorder_point' => $branchStock?->reorder_point,
                            'price_override' => $branchStock?->price_override,
                            'is_listed' => $branchStock === null ? null : (int) $branchStock->is_listed,
                        ]);
                    }
                });
        });
    }

    /**
     * The combined file: one row per batch, with the product columns repeated;
     * a product with no stock gets a single row with empty stock cells.
     * `$includeStock` false (no permission to see stock) leaves the stock cells empty.
     */
    public function catalog(bool $includeStock = true): StreamedResponse
    {
        $columns = CatalogTemplate::columns();

        return $this->download('produk-stok-'.now()->format('Ymd-His').'.csv', $columns, function ($out) use ($columns, $includeStock) {
            $settings = $includeStock
                ? BranchStock::query()->get()->keyBy(fn (BranchStock $row) => "{$row->branch_id}-{$row->product_id}")
                : collect();

            Product::query()
                ->with(['category', 'supplier', 'images'])
                ->orderBy('sku')
                ->chunk(500, function ($products) use ($out, $columns, $includeStock, $settings) {
                    $batches = $includeStock
                        ? InventoryBatch::query()
                            ->with('branch')
                            ->whereIn('product_id', $products->modelKeys())
                            ->where('quantity', '>', 0)
                            ->orderBy('branch_id')
                            ->orderBy('expires_at')
                            ->get()
                            ->filter(fn (InventoryBatch $batch) => $batch->branch !== null)
                            ->groupBy('product_id')
                        : collect();

                    foreach ($products as $product) {
                        $row = $this->productRow($product);
                        $productBatches = $batches->get($product->id, collect());

                        if ($productBatches->isEmpty()) {
                            $this->writeRow($out, $columns, $row);

                            continue;
                        }

                        foreach ($productBatches as $batch) {
                            $branchStock = $settings->get("{$batch->branch_id}-{$batch->product_id}");

                            $this->writeRow($out, $columns, $row + [
                                'branch' => $batch->branch->code,
                                'batch_number' => $batch->batch_number,
                                'expires_at' => $batch->expires_at->toDateString(),
                                'quantity' => $batch->quantity,
                                'batch_cost_price' => $batch->cost_price,
                                'received_at' => $batch->received_at?->toDateString(),
                                'reorder_point' => $branchStock?->reorder_point,
                                'price_override' => $branchStock?->price_override,
                                'is_listed' => $branchStock === null ? null : (int) $branchStock->is_listed,
                            ]);
                        }
                    }
                });
        });
    }

    public function catalogSample(): StreamedResponse
    {
        return $this->sample('template-produk-stok.csv', CatalogTemplate::columns(), CatalogTemplate::sampleRows());
    }

    public function productSample(): StreamedResponse
    {
        return $this->sample('template-produk.csv', ProductTemplate::COLUMNS, ProductTemplate::sampleRows());
    }

    public function stockSample(): StreamedResponse
    {
        return $this->sample('template-stok.csv', StockTemplate::COLUMNS, StockTemplate::sampleRows());
    }

    /**
     * @param  list<string>  $columns
     * @param  list<array<string, string>>  $rows
     */
    private function sample(string $fileName, array $columns, array $rows): StreamedResponse
    {
        return $this->download($fileName, $columns, function ($out) use ($columns, $rows) {
            foreach ($rows as $row) {
                $this->writeRow($out, $columns, $row);
            }
        });
    }

    /**
     * Starts with a UTF-8 byte-order mark so Excel reads accented text correctly.
     *
     * @param  list<string>  $columns
     * @param  callable(resource): void  $writeBody
     */
    private function download(string $fileName, array $columns, callable $writeBody): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $writeBody) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns, escape: '\\');
            $writeBody($out);
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  resource  $out
     * @param  list<string>  $columns
     * @param  array<string, mixed>  $values
     */
    private function writeRow($out, array $columns, array $values): void
    {
        fputcsv($out, array_map(fn (string $column) => $values[$column] ?? '', $columns), escape: '\\');
    }

    /**
     * @return array<string, mixed>
     */
    private function productRow(Product $product): array
    {
        return [
            'sku' => $product->sku,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category?->name,
            'supplier' => $product->supplier?->name,
            'unit' => $product->unit,
            'price' => $product->price,
            'old_price' => $product->old_price,
            'cost_price' => $product->cost_price,
            'drug_class' => $product->drug_class,
            'requires_prescription' => (int) $product->requires_prescription,
            'nie_bpom' => $product->nie_bpom,
            'composition' => $product->composition,
            'manufacturer' => $product->manufacturer,
            'blurb' => $product->blurb,
            'description' => $product->description,
            'indication' => $product->indication,
            'dosage' => $product->dosage,
            'side_effects' => $product->side_effects,
            'warning' => $product->warning,
            'storage' => $product->storage,
            'weight_grams' => $product->weight_grams,
            'max_qty_per_order' => $product->max_qty_per_order,
            'status' => $product->status,
            'image_url' => $product->images
                ->sortBy('position')
                ->map(fn ($image) => str_starts_with($image->path, '/') ? url($image->path) : $image->path)
                ->implode('|'),
        ];
    }
}

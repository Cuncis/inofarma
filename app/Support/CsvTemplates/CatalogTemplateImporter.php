<?php

namespace App\Support\CsvTemplates;

use App\Models\Product;
use RuntimeException;

/**
 * Imports the combined `produk-stok.csv` (see {@see CatalogTemplate}): the
 * product and its stock in one file, one row per product, branch and batch.
 *
 * It is a thin layer over {@see ProductTemplateImporter} and
 * {@see StockTemplateImporter}, so every rule of those carries over:
 *
 * - `sku` keys the product. Its product columns may sit on any row of that SKU,
 *   and an empty cell means "leave as is", never "clear". Two rows that give a
 *   column different values are an error for the whole SKU.
 * - A product that already exists needs only `sku` plus the columns to change,
 *   so a plain stock count is `sku, branch, batch_number, expires_at, quantity`.
 *   A new product needs every required product column.
 * - Rows without any stock cell only create or update the product.
 * - For each product and branch the stock rows are the complete batch list
 *   (see {@see StockTemplateImporter}): other batches go to zero, and a group
 *   with any bad row is not applied.
 *
 * The permission flags keep a stock-only user from editing products and a
 * catalogue-only user from touching stock.
 */
class CatalogTemplateImporter
{
    public function __construct(
        private readonly ?int $restrictToBranchId = null,
        private readonly ?int $userId = null,
        private readonly bool $canEditProducts = true,
        private readonly bool $canAdjustStock = true,
    ) {}

    /**
     * @return array{kind: string, created: int, updated: int, warnedInactive: int, imagesFailed: list<string>, groups: int, batches: int, zeroed: int, failed: list<array{row: int, message: string}>}
     *
     * @throws RuntimeException when the file is empty or has no `sku` column
     */
    public function import(string $path, string $fileName = 'produk-stok.csv'): array
    {
        ['header' => $header, 'rows' => $rows] = CsvTable::read($path);
        CsvTable::assertColumns($header, ['sku']);

        $summary = [
            'kind' => 'produk-stok', 'created' => 0, 'updated' => 0, 'warnedInactive' => 0, 'imagesFailed' => [],
            'groups' => 0, 'batches' => 0, 'zeroed' => 0, 'failed' => [],
        ];

        $products = new ProductTemplateImporter;
        $stock = new StockTemplateImporter($this->restrictToBranchId, $this->userId);
        $stockGroups = [];

        foreach ($this->rowsBySku($rows, $summary) as $skuRows) {
            $productOk = $this->importProduct($skuRows, $header, $products, $summary);

            foreach ($skuRows as ['line' => $line, 'cells' => $cells]) {
                if (! $this->hasStock($cells)) {
                    continue;
                }

                if (! $productOk) {
                    $summary['failed'][] = ['row' => $line, 'message' => 'Stok tidak diproses karena data produknya bermasalah.'];

                    continue;
                }

                try {
                    if (! $this->canAdjustStock) {
                        throw new RuntimeException('Anda tidak punya izin menyesuaikan stok.');
                    }

                    $stock->collect($this->stockCells($cells), $header, $stockGroups, $line);
                } catch (RuntimeException $e) {
                    $summary['failed'][] = ['row' => $line, 'message' => $e->getMessage()];
                }
            }
        }

        $products->finishImages($summary);
        $stock->applyGroups($stockGroups, $summary, "Impor produk dan stok CSV ({$fileName})");

        return $summary;
    }

    /**
     * @param  list<array{line: int, cells: array<string, string>}>  $rows
     * @param  array{failed: list<array{row: int, message: string}>}  $summary
     * @return array<string, list<array{line: int, cells: array<string, string>}>>
     */
    private function rowsBySku(array $rows, array &$summary): array
    {
        $bySku = [];

        foreach ($rows as $row) {
            $sku = $row['cells']['sku'];

            if ($sku === '') {
                $summary['failed'][] = ['row' => $row['line'], 'message' => 'Kolom sku wajib diisi.'];

                continue;
            }

            $bySku[mb_strtolower($sku)][] = $row;
        }

        return $bySku;
    }

    /**
     * Creates or updates the product from the merged product cells of its rows.
     *
     * @param  list<array{line: int, cells: array<string, string>}>  $skuRows
     * @param  list<string>  $header
     * @param  array<string, mixed>  $summary
     * @return bool false when the product could not be saved, so its stock must not be applied either
     */
    private function importProduct(array $skuRows, array $header, ProductTemplateImporter $products, array &$summary): bool
    {
        $firstLine = $skuRows[0]['line'];
        $sku = $skuRows[0]['cells']['sku'];

        try {
            $merged = $this->mergeProductCells($skuRows, $header);
        } catch (RuntimeException $e) {
            $summary['failed'][] = ['row' => $firstLine, 'message' => $e->getMessage()];

            return false;
        }

        $exists = Product::withTrashed()->where('sku', $sku)->exists();

        if ($exists && count($merged) === 1) {
            return true;
        }

        try {
            if (! $this->canEditProducts) {
                throw new RuntimeException('Anda tidak punya izin mengubah data produk.');
            }

            $products->importRow($merged, array_keys($merged), $summary, partial: true);
        } catch (\Throwable $e) {
            $summary['failed'][] = ['row' => $firstLine, 'message' => $e->getMessage()];

            return false;
        }

        return true;
    }

    /**
     * @param  list<array{line: int, cells: array<string, string>}>  $skuRows
     * @param  list<string>  $header
     * @return array<string, string> `sku` plus every product column that has a value
     *
     * @throws RuntimeException when rows disagree about a column
     */
    private function mergeProductCells(array $skuRows, array $header): array
    {
        $merged = ['sku' => $skuRows[0]['cells']['sku']];

        foreach (CatalogTemplate::productColumns() as $column) {
            if (! in_array($column, $header, true)) {
                continue;
            }

            $values = [];

            foreach ($skuRows as ['line' => $line, 'cells' => $cells]) {
                if ($cells[$column] !== '') {
                    $values[$cells[$column]][] = $line;
                }
            }

            if (count($values) > 1) {
                $lines = implode(', ', array_merge(...array_values($values)));

                throw new RuntimeException("Kolom {$column} berbeda antar baris untuk SKU {$merged['sku']} (baris {$lines}). Samakan atau kosongkan di baris lain.");
            }

            if ($values !== []) {
                $merged[$column] = (string) array_key_first($values);
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, string>  $cells
     */
    private function hasStock(array $cells): bool
    {
        foreach (CatalogTemplate::STOCK_COLUMNS as $column) {
            if (($cells[$column] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $cells
     * @return array<string, string>
     */
    private function stockCells(array $cells): array
    {
        $stockCells = ['sku' => $cells['sku'], 'branch_code' => $cells['branch'] ?? ''];

        foreach (array_slice(CatalogTemplate::STOCK_COLUMNS, 1) as $column) {
            $stockCells[$column] = $cells[$column] ?? '';
        }

        return $stockCells;
    }
}

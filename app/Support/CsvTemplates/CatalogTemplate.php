<?php

namespace App\Support\CsvTemplates;

/**
 * Column layout of the combined `produk-stok.csv`: every product column, then
 * the stock columns, one row per product, branch and batch.
 *
 * `branch` takes a branch code or name. The product columns repeat on each row
 * of the same SKU; a later row of a known product may leave them empty.
 */
class CatalogTemplate
{
    public const STOCK_COLUMNS = [
        'branch', 'batch_number', 'expires_at', 'quantity', 'is_listed',
    ];

    /** @return list<string> */
    public static function columns(): array
    {
        return [...ProductTemplate::COLUMNS, ...self::STOCK_COLUMNS];
    }

    /** @return list<string> product columns without the SKU, which keys the row */
    public static function productColumns(): array
    {
        return array_values(array_filter(ProductTemplate::COLUMNS, fn (string $column) => $column !== 'sku'));
    }

    /**
     * @return list<array<string, string>>
     */
    public static function sampleRows(): array
    {
        [$product, $second] = ProductTemplate::sampleRows();

        $emptyProduct = array_fill_keys(ProductTemplate::COLUMNS, '');

        return [
            $product + [
                'branch' => 'CB-001', 'batch_number' => 'BN2401A', 'expires_at' => '2027-06-30', 'quantity' => '120',
                'is_listed' => '1',
            ],
            ['sku' => 'PRD-001'] + $emptyProduct + [
                'branch' => 'CB-001', 'batch_number' => '', 'expires_at' => '2027-12-31', 'quantity' => '80',
                'is_listed' => '',
            ],
            ['sku' => 'PRD-001'] + $emptyProduct + [
                'branch' => 'CB-002', 'batch_number' => 'BN2401A', 'expires_at' => '2027-06-30', 'quantity' => '60',
                'is_listed' => '',
            ],
            $second + [
                'branch' => '', 'batch_number' => '', 'expires_at' => '', 'quantity' => '',
                'is_listed' => '',
            ],
        ];
    }
}

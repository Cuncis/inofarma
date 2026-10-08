<?php

namespace App\Support\CsvTemplates;

/**
 * Column layout of `stok.csv`: one row per product, branch and batch.
 */
class StockTemplate
{
    public const COLUMNS = [
        'sku', 'branch_code', 'batch_number', 'expires_at', 'quantity', 'batch_cost_price',
        'received_at', 'reorder_point', 'price_override', 'is_listed',
    ];

    public const REQUIRED = ['sku', 'branch_code', 'batch_number', 'expires_at', 'quantity'];

    /**
     * @return list<array<string, string>>
     */
    public static function sampleRows(): array
    {
        return [
            [
                'sku' => 'PRD-001', 'branch_code' => 'CB-001', 'batch_number' => 'BN2401A', 'expires_at' => '2027-06-30',
                'quantity' => '120', 'batch_cost_price' => '3500', 'received_at' => '2026-09-01',
                'reorder_point' => '30', 'price_override' => '', 'is_listed' => '1',
            ],
            [
                'sku' => 'PRD-001', 'branch_code' => 'CB-001', 'batch_number' => 'BN2402B', 'expires_at' => '2027-12-31',
                'quantity' => '80', 'batch_cost_price' => '3600', 'received_at' => '2026-10-01',
                'reorder_point' => '', 'price_override' => '', 'is_listed' => '',
            ],
        ];
    }
}

<?php

namespace App\Support\CsvTemplates;

/**
 * Column layout of `stok.csv`: one row per product, branch and batch.
 */
class StockTemplate
{
    public const COLUMNS = [
        'sku', 'branch_code', 'batch_number', 'expires_at', 'quantity', 'is_listed',
    ];

    public const REQUIRED = ['sku', 'branch_code', 'expires_at', 'quantity'];

    /**
     * @return list<array<string, string>>
     */
    public static function sampleRows(): array
    {
        return [
            [
                'sku' => 'PRD-001', 'branch_code' => 'CB-001', 'batch_number' => 'BN2401A', 'expires_at' => '2027-06-30',
                'quantity' => '120', 'is_listed' => '1',
            ],
            [
                'sku' => 'PRD-001', 'branch_code' => 'CB-001', 'batch_number' => '', 'expires_at' => '2027-12-31',
                'quantity' => '80', 'is_listed' => '',
            ],
        ];
    }
}

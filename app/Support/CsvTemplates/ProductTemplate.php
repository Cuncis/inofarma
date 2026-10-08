<?php

namespace App\Support\CsvTemplates;

/**
 * Column layout of `produk.csv`, shared by the importer, the exporter and the
 * downloadable sample so the three can never drift apart.
 */
class ProductTemplate
{
    public const COLUMNS = [
        'sku', 'name', 'slug', 'category', 'supplier', 'unit', 'price', 'old_price', 'cost_price',
        'drug_class', 'requires_prescription', 'nie_bpom', 'composition', 'manufacturer', 'blurb',
        'description', 'indication', 'dosage', 'side_effects', 'warning', 'storage', 'weight_grams',
        'max_qty_per_order', 'status', 'image_url',
    ];

    public const REQUIRED = ['sku', 'name', 'category', 'unit', 'price', 'drug_class', 'weight_grams'];

    public const DRUG_CLASSES = ['bebas', 'bebas terbatas', 'keras', 'non-obat'];

    public const STORAGES = ['suhu ruang', 'sejuk', 'dingin'];

    public const STATUSES = ['aktif', 'nonaktif', 'arsip'];

    /**
     * @return list<array<string, string>>
     */
    public static function sampleRows(): array
    {
        return [
            [
                'sku' => 'PRD-001', 'name' => 'Paracetamol 500 mg Strip 10', 'slug' => 'paracetamol-500-mg-strip-10',
                'category' => 'Obat Bebas', 'supplier' => '', 'unit' => 'Strip', 'price' => '5000',
                'old_price' => '', 'cost_price' => '3500', 'drug_class' => 'bebas', 'requires_prescription' => '0',
                'nie_bpom' => 'DBL1234567890A1', 'composition' => 'Paracetamol 500 mg', 'manufacturer' => 'PT Contoh Farma',
                'blurb' => 'Pereda demam dan nyeri.', 'description' => 'Obat penurun demam dan pereda nyeri ringan.',
                'indication' => 'Demam, sakit kepala, nyeri ringan', 'dosage' => '3 kali sehari 1 tablet',
                'side_effects' => '', 'warning' => '', 'storage' => 'suhu ruang', 'weight_grams' => '20',
                'max_qty_per_order' => '5', 'status' => 'aktif', 'image_url' => 'https://contoh.com/foto/paracetamol.jpg',
            ],
            [
                'sku' => 'PRD-002', 'name' => 'Vitamin C 1000 mg Botol 30', 'slug' => '',
                'category' => 'Suplemen', 'supplier' => '', 'unit' => 'Botol', 'price' => '85000',
                'old_price' => '95000', 'cost_price' => '60000', 'drug_class' => 'non-obat', 'requires_prescription' => '0',
                'nie_bpom' => '', 'composition' => 'Vitamin C 1000 mg', 'manufacturer' => '',
                'blurb' => '', 'description' => '', 'indication' => '', 'dosage' => '',
                'side_effects' => '', 'warning' => '', 'storage' => 'suhu ruang', 'weight_grams' => '150',
                'max_qty_per_order' => '', 'status' => 'aktif', 'image_url' => 'https://contoh.com/foto/vit-c-1.jpg|https://contoh.com/foto/vit-c-2.jpg',
            ],
        ];
    }
}

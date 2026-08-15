<?php

namespace App\Support;

/**
 * Seed catalogue.
 *
 * The starting set of products the session store is filled with, and the
 * canonical category list. This is where a database would take over: replace
 * the callers of `ProductStore` with an Eloquent model and this file goes away.
 */
class Catalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function products(): array
    {
        return [
            self::product('PRD-001', 'Paracetamol 500mg', 'Obat Bebas', 1, 12500, 482, 1240, '4.8', 'Aktif', 'Strip', false, 15000,
                'Meredakan demam dan nyeri ringan hingga sedang. Aman dikonsumsi setelah makan.'),
            self::product('PRD-002', 'Amoxicillin 500mg', 'Obat Keras', 2, 38000, 126, 860, '4.6', 'Aktif', 'Strip', true, null,
                'Antibiotik untuk infeksi bakteri. Wajib menyertakan resep dokter saat memesan.'),
            self::product('PRD-003', 'Vitamin C 1000mg', 'Suplemen', 3, 75000, 0, 2130, '4.9', 'Habis', 'Botol', false, 89000,
                'Membantu menjaga daya tahan tubuh. Dikonsumsi satu tablet per hari.'),
            self::product('PRD-004', 'Masker Medis 3 Ply', 'Alat Kesehatan', 4, 45000, 1520, 4210, '4.7', 'Aktif', 'Box', false, null,
                'Masker tiga lapis dengan filter, nyaman dipakai seharian dan tidak mudah lepas.'),
            self::product('PRD-005', 'Hand Sanitizer 500ml', 'Antiseptik', 5, 32000, 64, 980, '4.5', 'Stok Menipis', 'Botol', false, 40000,
                'Membunuh kuman tanpa perlu dibilas, dengan pelembap agar tangan tidak kering.'),
            self::product('PRD-006', 'Termometer Digital', 'Alat Kesehatan', 6, 125000, 213, 540, '4.8', 'Aktif', 'Pcs', false, null,
                'Pengukur suhu tubuh digital dengan hasil akurat dalam waktu sepuluh detik.'),
            self::product('PRD-007', 'Vitamin D3 1000 IU', 'Suplemen', 7, 68000, 340, 720, '4.7', 'Aktif', 'Botol', false, null,
                'Mendukung kesehatan tulang dan sistem imun, terutama bagi yang jarang terkena sinar matahari.'),
            self::product('PRD-008', 'Minyak Kayu Putih 60ml', 'Obat Bebas', 8, 24000, 610, 1580, '4.6', 'Aktif', 'Botol', false, 30000,
                'Menghangatkan badan dan meredakan perut kembung. Cocok dibawa bepergian.'),
            self::product('PRD-009', 'Plester Luka Isi 20', 'Alat Kesehatan', 9, 18500, 890, 1120, '4.4', 'Aktif', 'Box', false, null,
                'Plester elastis anti air untuk luka kecil, melekat kuat namun mudah dilepas.'),
            self::product('PRD-010', 'Obat Batuk Sirup 100ml', 'Obat Bebas', 10, 29500, 275, 940, '4.5', 'Aktif', 'Botol', false, 35000,
                'Meredakan batuk berdahak dan melegakan tenggorokan. Tersedia rasa jeruk.'),
            self::product('PRD-011', 'Sunscreen SPF 50', 'Perawatan Kulit', 11, 96000, 158, 660, '4.8', 'Aktif', 'Botol', false, null,
                'Perlindungan harian dari sinar UVA dan UVB, ringan dan tidak meninggalkan whitecast.'),
            self::product('PRD-012', 'Alkohol Swab Isi 100', 'Antiseptik', 12, 21000, 430, 810, '4.3', 'Aktif', 'Box', false, null,
                'Tisu alkohol steril sekali pakai untuk membersihkan kulit sebelum penyuntikan.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            self::category('Obat Bebas', 'obat-bebas', 1, 'Aktif',
                'Obat yang dapat dibeli tanpa resep dokter dan dijual bebas di apotek maupun toko obat berizin.'),
            self::category('Obat Keras', 'obat-keras', 2, 'Aktif',
                'Obat yang hanya boleh diserahkan dengan resep dokter dan diawasi apoteker.'),
            self::category('Suplemen', 'suplemen', 3, 'Aktif',
                'Vitamin dan produk penunjang daya tahan tubuh untuk konsumsi harian.'),
            self::category('Alat Kesehatan', 'alat-kesehatan', 4, 'Aktif',
                'Perlengkapan medis habis pakai maupun alat ukur untuk perawatan di rumah.'),
            self::category('Antiseptik', 'antiseptik', 6, 'Aktif',
                'Produk pembersih dan pembunuh kuman untuk kulit maupun permukaan.'),
            self::category('Perawatan Kulit', 'perawatan-kulit', 10, 'Nonaktif',
                'Produk perawatan kulit harian termasuk pelindung dari sinar matahari.'),
        ];
    }

    /**
     * Just the category names, for dropdowns and validation.
     *
     * @return list<string>
     */
    public static function categoryNames(): array
    {
        return array_column(self::categories(), 'name');
    }

    /**
     * @return list<string>
     */
    public static function categoryStatuses(): array
    {
        return ['Aktif', 'Nonaktif'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function category(
        string $name,
        string $slug,
        int $image,
        string $status,
        string $description,
    ): array {
        return [
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
            'image' => "/media/images/small/img-{$image}.jpg",
            'status' => $status,
            'description' => $description,
        ];
    }

    /**
     * @return list<string>
     */
    public static function units(): array
    {
        return ['Strip', 'Botol', 'Box', 'Tablet', 'Pcs'];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return ['Aktif', 'Stok Menipis', 'Habis', 'Nonaktif'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function product(
        string $id,
        string $name,
        string $category,
        int $image,
        int $price,
        int $stock,
        int $sold,
        string $rating,
        string $status,
        string $unit,
        bool $prescription,
        ?int $oldPrice,
        string $blurb,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'image' => "/media/images/product/p-{$image}.png",
            'price' => $price,
            'oldPrice' => $oldPrice,
            'stock' => $stock,
            'sold' => $sold,
            'rating' => $rating,
            'status' => $status,
            'unit' => $unit,
            'prescription' => $prescription,
            'blurb' => $blurb,
        ];
    }
}

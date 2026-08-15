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
    /** Which seller stocks each seed product. */
    private const PRODUCT_SELLERS = [
        'PRD-001' => 'Apotek Sehat Bersama',
        'PRD-002' => 'Apotek Sehat Bersama',
        'PRD-003' => 'Toko Obat Mandiri',
        'PRD-004' => 'Farmasi Nusantara',
        'PRD-005' => 'Griya Farma',
        'PRD-006' => 'Farmasi Nusantara',
        'PRD-007' => 'Toko Obat Mandiri',
        'PRD-008' => 'Apotek Sehat Bersama',
        'PRD-009' => 'Farmasi Nusantara',
        'PRD-010' => 'Apotek Sehat Bersama',
        'PRD-011' => 'Griya Farma',
        'PRD-012' => 'Griya Farma',
    ];

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
     * @return list<array<string, mixed>>
     */
    public static function sellers(): array
    {
        return [
            self::seller('SEL-001', 'Apotek Sehat Bersama', 'Kirana Wijaya', 'sehatbersama@mail.com', '+62 21 5551 0001', 'SIA/2024/00181', 'Jakarta Selatan', 'Jl. Jend. Sudirman Kav. 52-53', 'nike', 'Terverifikasi', '4.8', '14 Feb 2024'),
            self::seller('SEL-002', 'Toko Obat Mandiri', 'Rizky Ananda', 'obatmandiri@mail.com', '+62 22 5552 0002', 'SIA/2024/00224', 'Bandung', 'Jl. Asia Afrika No. 120', 'dyson', 'Terverifikasi', '4.5', '30 Apr 2024'),
            self::seller('SEL-003', 'Farmasi Nusantara', 'Dinda Puspita', 'farmasinusantara@mail.com', '+62 31 5553 0003', 'SIA/2024/00310', 'Surabaya', 'Jl. Pemuda No. 41', 'huawei', 'Terverifikasi', '4.9', '08 Jul 2024'),
            self::seller('SEL-004', 'Griya Farma', 'Bagas Saputra', 'griyafarma@mail.com', '+62 274 5554 0004', 'SIA/2025/00077', 'Yogyakarta', 'Jl. Malioboro No. 18', 'gopro', 'Menunggu', '4.1', '19 Mei 2025'),
            self::seller('SEL-005', 'Apotek Melati', 'Anisa Rahmawati', 'apotekmelati@mail.com', '+62 61 5555 0005', 'SIA/2025/00142', 'Medan', 'Jl. Gatot Subroto No. 77', 'zara', 'Menunggu', '0.0', '05 Agu 2025'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function sellerNames(): array
    {
        return array_column(self::sellers(), 'name');
    }

    /**
     * @return list<string>
     */
    public static function sellerStatuses(): array
    {
        return ['Menunggu', 'Terverifikasi', 'Nonaktif'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function seller(
        string $id,
        string $name,
        string $owner,
        string $email,
        string $phone,
        string $license,
        string $city,
        string $address,
        string $logo,
        string $status,
        string $rating,
        string $joined,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'owner' => $owner,
            'email' => $email,
            'phone' => $phone,
            'license' => $license,
            'city' => $city,
            'address' => $address,
            'logo' => "/media/images/seller/{$logo}.svg",
            'status' => $status,
            'rating' => $rating,
            'joined' => $joined,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function customers(): array
    {
        return [
            self::customer('CUS-001', 'Kirana Wijaya', 'kirana.wijaya@mail.com', '+62 812-3456-7890', 'Jakarta Barat', 'Jl. Kebon Jeruk Raya No. 27', 1, 'Aktif', '12 Jan 2024'),
            self::customer('CUS-002', 'Rizky Ananda', 'rizky.ananda@mail.com', '+62 813-2233-4455', 'Bandung', 'Jl. Asia Afrika No. 88', 2, 'Aktif', '03 Mar 2024'),
            self::customer('CUS-003', 'Dinda Puspita', 'dinda.puspita@mail.com', '+62 856-7788-9900', 'Surabaya', 'Jl. Pemuda No. 14', 3, 'Aktif', '21 Jun 2024'),
            self::customer('CUS-004', 'Bagas Saputra', 'bagas.saputra@mail.com', '+62 878-1122-3344', 'Yogyakarta', 'Jl. Malioboro No. 5', 4, 'Nonaktif', '09 Sep 2024'),
            self::customer('CUS-005', 'Sari Wulandari', 'sari.wulandari@mail.com', '+62 811-5566-7788', 'Semarang', 'Jl. Pandanaran No. 62', 5, 'Aktif', '17 Nov 2024'),
            self::customer('CUS-006', 'Anisa Rahmawati', 'anisa.rahmawati@mail.com', '+62 852-9900-1122', 'Medan', 'Jl. Gatot Subroto No. 30', 6, 'Aktif', '02 Agu 2025'),
        ];
    }

    /**
     * Orders, keyed to their customer by email so a rename cannot break the link.
     *
     * Line items snapshot the product name and unit price: an order is a
     * historical record, so editing or deleting a product later must not rewrite
     * what somebody was actually charged.
     *
     * @return list<array<string, mixed>>
     */
    public static function orders(): array
    {
        return [
            self::order('INO-2451', 'kirana.wijaya@mail.com', '14 Agu 2025', 'Transfer Bank', 'Selesai', 25000, [
                ['productId' => 'PRD-001', 'name' => 'Paracetamol 500mg', 'qty' => 12, 'price' => 12500],
                ['productId' => 'PRD-004', 'name' => 'Masker Medis 3 Ply', 'qty' => 6, 'price' => 45000],
                ['productId' => 'PRD-009', 'name' => 'Plester Luka Isi 20', 'qty' => 2, 'price' => 18500],
            ]),
            self::order('INO-2450', 'rizky.ananda@mail.com', '14 Agu 2025', 'GoPay', 'Diproses', 25000, [
                ['productId' => 'PRD-003', 'name' => 'Vitamin C 1000mg', 'qty' => 10, 'price' => 75000],
                ['productId' => 'PRD-007', 'name' => 'Vitamin D3 1000 IU', 'qty' => 7, 'price' => 68000],
            ]),
            self::order('INO-2449', 'dinda.puspita@mail.com', '13 Agu 2025', 'OVO', 'Dikirim', 20000, [
                ['productId' => 'PRD-005', 'name' => 'Hand Sanitizer 500ml', 'qty' => 5, 'price' => 32000],
                ['productId' => 'PRD-012', 'name' => 'Alkohol Swab Isi 100', 'qty' => 4, 'price' => 21000],
            ]),
            self::order('INO-2448', 'bagas.saputra@mail.com', '13 Agu 2025', 'DANA', 'Dibatalkan', 12000, [
                ['productId' => 'PRD-008', 'name' => 'Minyak Kayu Putih 60ml', 'qty' => 2, 'price' => 24000],
                ['productId' => 'PRD-010', 'name' => 'Obat Batuk Sirup 100ml', 'qty' => 1, 'price' => 29500],
            ]),
            self::order('INO-2447', 'sari.wulandari@mail.com', '12 Agu 2025', 'Transfer Bank', 'Selesai', 25000, [
                ['productId' => 'PRD-011', 'name' => 'Sunscreen SPF 50', 'qty' => 5, 'price' => 96000],
                ['productId' => 'PRD-006', 'name' => 'Termometer Digital', 'qty' => 1, 'price' => 125000],
            ]),
            self::order('INO-2446', 'kirana.wijaya@mail.com', '10 Agu 2025', 'Transfer Bank', 'Selesai', 18000, [
                ['productId' => 'PRD-002', 'name' => 'Amoxicillin 500mg', 'qty' => 5, 'price' => 38000],
                ['productId' => 'PRD-001', 'name' => 'Paracetamol 500mg', 'qty' => 8, 'price' => 12500],
            ]),
            self::order('INO-2445', 'dinda.puspita@mail.com', '08 Agu 2025', 'OVO', 'Menunggu Pembayaran', 15000, [
                ['productId' => 'PRD-009', 'name' => 'Plester Luka Isi 20', 'qty' => 3, 'price' => 18500],
                ['productId' => 'PRD-012', 'name' => 'Alkohol Swab Isi 100', 'qty' => 4, 'price' => 21000],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    public static function orderStatuses(): array
    {
        return ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
    }

    /**
     * @return list<string>
     */
    public static function paymentMethods(): array
    {
        return ['Transfer Bank', 'GoPay', 'OVO', 'DANA', 'Tunai'];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private static function order(
        string $id,
        string $customerEmail,
        string $date,
        string $payment,
        string $status,
        int $shipping,
        array $items,
    ): array {
        return [
            'id' => $id,
            'customerEmail' => $customerEmail,
            'date' => $date,
            'payment' => $payment,
            'status' => $status,
            'shipping' => $shipping,
            'note' => '',
            'items' => $items,
        ];
    }

    /**
     * @return list<string>
     */
    public static function customerStatuses(): array
    {
        return ['Aktif', 'Nonaktif'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function customer(
        string $id,
        string $name,
        string $email,
        string $phone,
        string $city,
        string $address,
        int $avatar,
        string $status,
        string $joined,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'address' => $address,
            'avatar' => "/media/images/users/avatar-{$avatar}.jpg",
            'status' => $status,
            'joined' => $joined,
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
            'seller' => self::PRODUCT_SELLERS[$id] ?? 'Apotek Sehat Bersama',
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

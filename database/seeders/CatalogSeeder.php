<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Katalog contoh: kategori, pemasok, produk, gambar, dan sebaran stok.
 *
 * Data yang sama dengan yang dipakai prototipe sebelum ada basis data, supaya
 * tampilan admin tidak berubah saat penyimpanan berpindah. Angka `stock` pada
 * daftar produk di bawah adalah **total nasional**; seeder membagikannya ke
 * sepuluh cabang, karena stok tidak pernah menjadi milik produk saja.
 */
class CatalogSeeder extends Seeder
{
    /**
     * name, slug, status, deskripsi. `image_path` is derived from the slug
     * (see `seedCategories()`) rather than listed here, since it always
     * points at the storefront's curated `/media/images/categories/{slug}.png`
     * badge art. The homepage category shortcuts and this admin-managed
     * list intentionally share the exact same 7 categories and artwork.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private const CATEGORIES = [
        ['Kesehatan', 'kesehatan', 'aktif',
            'Obat umum dan kebutuhan kesehatan sehari-hari, termasuk yang memerlukan resep dokter.'],
        ['Kebutuhan Keluarga', 'kebutuhan-keluarga', 'aktif',
            'Produk kebersihan dan pembunuh kuman untuk kulit maupun permukaan, dibutuhkan setiap rumah tangga.'],
        ['Alat Kesehatan', 'alat-kesehatan', 'aktif',
            'Perlengkapan medis habis pakai maupun alat ukur untuk perawatan di rumah.'],
        ['Perawatan Tubuh', 'perawatan-tubuh', 'aktif',
            'Produk perawatan tubuh harian termasuk pelindung dari sinar matahari.'],
        ['Obat Tradisional', 'obat-tradisional', 'aktif',
            'Obat herbal dan racikan tradisional turun-temurun untuk keluhan ringan sehari-hari.'],
        ['Vitamin & Suplemen', 'vitamin-suplemen', 'aktif',
            'Vitamin dan produk penunjang daya tahan tubuh untuk konsumsi harian.'],
        ['Obat Bebas', 'obat-bebas', 'aktif',
            'Obat yang dapat dibeli tanpa resep dokter dan dijual bebas di apotek maupun toko obat berizin.'],
    ];

    /**
     * code, nama, narahubung, email, telepon, izin, kota, alamat, status, bergabung
     *
     * @var list<array<int, string>>
     */
    private const SUPPLIERS = [
        ['SEL-001', 'Apotek Sehat Bersama', 'Kirana Wijaya', 'sehatbersama@mail.com', '+62 21 5551 0001', 'SIA/2024/00181', 'Jakarta Selatan', 'Jl. Jend. Sudirman Kav. 52-53', 'aktif', '2024-02-14'],
        ['SEL-002', 'Toko Obat Mandiri', 'Rizky Ananda', 'obatmandiri@mail.com', '+62 22 5552 0002', 'SIA/2024/00224', 'Bandung', 'Jl. Asia Afrika No. 120', 'aktif', '2024-04-30'],
        ['SEL-003', 'Farmasi Nusantara', 'Dinda Puspita', 'farmasinusantara@mail.com', '+62 31 5553 0003', 'SIA/2024/00310', 'Surabaya', 'Jl. Pemuda No. 41', 'aktif', '2024-07-08'],
        ['SEL-004', 'Griya Farma', 'Bagas Saputra', 'griyafarma@mail.com', '+62 274 5554 0004', 'SIA/2025/00077', 'Yogyakarta', 'Jl. Malioboro No. 18', 'aktif', '2025-05-19'],
        ['SEL-005', 'Apotek Melati', 'Anisa Rahmawati', 'apotekmelati@mail.com', '+62 61 5555 0005', 'SIA/2025/00142', 'Medan', 'Jl. Gatot Subroto No. 77', 'nonaktif', '2025-08-05'],
    ];

    /**
     * sku, nama, kategori, pemasok, gambar, harga, harga coret, total stok,
     * terjual, rating, satuan, resep, deskripsi
     *
     * @var list<array<int, mixed>>
     */
    private const PRODUCTS = [
        ['PRD-001', 'Paracetamol 500mg', 'Obat Bebas', 'Apotek Sehat Bersama', 1, 12500, 15000, 482, 1240, '4.8', 'Strip', false,
            'Meredakan demam dan nyeri ringan hingga sedang. Aman dikonsumsi setelah makan.'],
        ['PRD-002', 'Amoxicillin 500mg', 'Kesehatan', 'Apotek Sehat Bersama', 2, 38000, null, 126, 860, '4.6', 'Strip', true,
            'Antibiotik untuk infeksi bakteri. Wajib menyertakan resep dokter saat memesan.'],
        ['PRD-003', 'Vitamin C 1000mg', 'Vitamin & Suplemen', 'Toko Obat Mandiri', 3, 75000, 89000, 0, 2130, '4.9', 'Botol', false,
            'Membantu menjaga daya tahan tubuh. Dikonsumsi satu tablet per hari.'],
        ['PRD-004', 'Masker Medis 3 Ply', 'Alat Kesehatan', 'Farmasi Nusantara', 4, 45000, null, 1520, 4210, '4.7', 'Box', false,
            'Masker tiga lapis dengan filter, nyaman dipakai seharian dan tidak mudah lepas.'],
        ['PRD-005', 'Hand Sanitizer 500ml', 'Kebutuhan Keluarga', 'Griya Farma', 5, 32000, 40000, 64, 980, '4.5', 'Botol', false,
            'Membunuh kuman tanpa perlu dibilas, dengan pelembap agar tangan tidak kering.'],
        ['PRD-006', 'Termometer Digital', 'Alat Kesehatan', 'Farmasi Nusantara', 6, 125000, null, 213, 540, '4.8', 'Pcs', false,
            'Pengukur suhu tubuh digital dengan hasil akurat dalam waktu sepuluh detik.'],
        ['PRD-007', 'Vitamin D3 1000 IU', 'Vitamin & Suplemen', 'Toko Obat Mandiri', 7, 68000, null, 340, 720, '4.7', 'Botol', false,
            'Mendukung kesehatan tulang dan sistem imun, terutama bagi yang jarang terkena sinar matahari.'],
        ['PRD-008', 'Minyak Kayu Putih 60ml', 'Obat Tradisional', 'Apotek Sehat Bersama', 8, 24000, 30000, 610, 1580, '4.6', 'Botol', false,
            'Menghangatkan badan dan meredakan perut kembung. Cocok dibawa bepergian.'],
        ['PRD-009', 'Plester Luka Isi 20', 'Alat Kesehatan', 'Farmasi Nusantara', 9, 18500, null, 890, 1120, '4.4', 'Box', false,
            'Plester elastis anti air untuk luka kecil, melekat kuat namun mudah dilepas.'],
        ['PRD-010', 'Obat Batuk Sirup 100ml', 'Obat Bebas', 'Apotek Sehat Bersama', 10, 29500, 35000, 275, 940, '4.5', 'Botol', false,
            'Meredakan batuk berdahak dan melegakan tenggorokan. Tersedia rasa jeruk.'],
        ['PRD-011', 'Sunscreen SPF 50', 'Perawatan Tubuh', 'Griya Farma', 11, 96000, null, 158, 660, '4.8', 'Botol', false,
            'Perlindungan harian dari sinar UVA dan UVB, ringan dan tidak meninggalkan whitecast.'],
        ['PRD-012', 'Alkohol Swab Isi 100', 'Kebutuhan Keluarga', 'Griya Farma', 12, 21000, null, 430, 810, '4.3', 'Box', false,
            'Tisu alkohol steril sekali pakai untuk membersihkan kulit sebelum penyuntikan.'],
    ];

    /**
     * Data farmasi (Fase 4.2), keyed by SKU. Not every seeded product needs to
     * be this complete — one fully-fleshed product is what the "Selesai bila"
     * criterion actually asks for — but filling in what a real label would say
     * for each keeps the admin/shop screens from looking half-built during a
     * demo. PRD-010 is deliberately "bebas terbatas" so a P1–P6 warning shows
     * somewhere in the seed data, not just in a test fixture.
     *
     * @var array<string, array{nie: ?string, composition: ?string, indication: ?string, dosage: ?string, sideEffects: ?string, warning: ?string, manufacturer: string, storage: string, maxQty: ?int, drugClass?: string}>
     */
    private const PHARMA = [
        'PRD-001' => ['nie' => 'DBL7813704133A1', 'composition' => 'Tiap tablet mengandung Paracetamol 500 mg.', 'indication' => 'Meredakan demam dan nyeri ringan hingga sedang seperti sakit kepala dan nyeri otot.', 'dosage' => 'Dewasa: 1 tablet, 3-4 kali sehari setelah makan. Maksimal 8 tablet per hari.', 'sideEffects' => 'Jarang: mual atau ruam kulit. Hindari melebihi dosis anjuran karena berisiko pada fungsi hati.', 'warning' => null, 'manufacturer' => 'PT Kimia Farma Tbk', 'storage' => 'suhu ruang', 'maxQty' => 5],
        'PRD-002' => ['nie' => 'DKL0332701910A1', 'composition' => 'Tiap kapsul mengandung Amoxicillin trihydrate setara Amoxicillin 500 mg.', 'indication' => 'Infeksi bakteri pada saluran napas, saluran kemih, dan kulit.', 'dosage' => 'Sesuai resep dokter, umumnya 1 kapsul setiap 8 jam selama 5-7 hari.', 'sideEffects' => 'Mual, diare, reaksi alergi pada individu yang sensitif terhadap penisilin.', 'warning' => null, 'manufacturer' => 'PT Sanbe Farma', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-003' => ['nie' => 'SD202312345', 'composition' => 'Tiap tablet effervescent mengandung Vitamin C (Asam Askorbat) 1000 mg.', 'indication' => 'Membantu memenuhi kebutuhan vitamin C harian dan menjaga daya tahan tubuh.', 'dosage' => 'Dewasa: 1 tablet per hari, dilarutkan dalam segelas air.', 'sideEffects' => 'Gangguan pencernaan ringan bila dikonsumsi berlebihan.', 'warning' => null, 'manufacturer' => 'PT Kalbe Farma Tbk', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-004' => ['nie' => null, 'composition' => 'Masker bedah 3 lapis dengan filter tengah.', 'indication' => 'Perlindungan pernapasan sehari-hari dari droplet dan partikel debu.', 'dosage' => 'Sekali pakai, ganti setiap 4-6 jam pemakaian.', 'sideEffects' => null, 'warning' => null, 'manufacturer' => 'PT Selaras Cipta Medika', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-005' => ['nie' => 'NA18211200247', 'composition' => 'Ethyl alcohol 70%, aloe vera extract, glycerin.', 'indication' => 'Membunuh kuman pada tangan tanpa perlu dibilas air.', 'dosage' => 'Tuang secukupnya, ratakan ke seluruh permukaan tangan hingga kering.', 'sideEffects' => 'Kulit kering pada pemakaian berlebihan.', 'warning' => null, 'manufacturer' => 'PT Griya Farma', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-006' => ['nie' => null, 'composition' => 'Termometer digital non-kontak.', 'indication' => 'Mengukur suhu tubuh untuk memantau demam.', 'dosage' => 'Arahkan ke dahi dari jarak 3-5 cm, tekan tombol ukur.', 'sideEffects' => null, 'warning' => null, 'manufacturer' => 'Omron Healthcare', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-007' => ['nie' => 'SD201845678', 'composition' => 'Tiap kapsul lunak mengandung Cholecalciferol (Vitamin D3) 1000 IU.', 'indication' => 'Mendukung kesehatan tulang dan sistem imun.', 'dosage' => 'Dewasa: 1 kapsul per hari, dikonsumsi bersama makanan.', 'sideEffects' => 'Jarang, pada dosis wajar.', 'warning' => null, 'manufacturer' => 'PT Kalbe Farma Tbk', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-008' => ['nie' => 'QL201234561', 'composition' => 'Oleum Cajuputi (minyak kayu putih) 100%.', 'indication' => 'Menghangatkan badan, meredakan perut kembung dan masuk angin.', 'dosage' => 'Oleskan secukupnya pada perut atau punggung, pijat perlahan.', 'sideEffects' => 'Iritasi kulit bila kontak langsung dengan mata.', 'warning' => null, 'manufacturer' => 'PT Sehat Bersama', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-009' => ['nie' => null, 'composition' => 'Plester elastis dengan bantalan kasa steril.', 'indication' => 'Menutup luka kecil dan lecet.', 'dosage' => 'Tempelkan pada luka yang sudah dibersihkan, ganti setiap hari.', 'sideEffects' => null, 'warning' => null, 'manufacturer' => 'PT Farmasi Nusantara', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-010' => ['nie' => 'DTL8912345637A1', 'composition' => 'Tiap 5 ml mengandung Dextromethorphan HBr 15 mg.', 'indication' => 'Meredakan batuk tidak berdahak.', 'dosage' => 'Dewasa: 1 sendok takar (5 ml), 3 kali sehari.', 'sideEffects' => 'Mengantuk, pusing ringan. Hindari mengemudi setelah minum obat ini.', 'warning' => 'Awas! Obat Keras. Bacalah aturan pemakaiannya.', 'manufacturer' => 'PT Sehat Bersama', 'storage' => 'suhu ruang', 'maxQty' => 3, 'drugClass' => 'bebas terbatas'],
        'PRD-011' => ['nie' => 'NA18211500123', 'composition' => 'Homosalate, Octocrylene, Zinc Oxide, Niacinamide.', 'indication' => 'Melindungi kulit dari paparan sinar UVA dan UVB.', 'dosage' => 'Oleskan merata ke wajah dan leher 15 menit sebelum beraktivitas di luar ruangan, ulangi setiap 3-4 jam.', 'sideEffects' => 'Iritasi ringan pada kulit sensitif.', 'warning' => null, 'manufacturer' => 'PT Griya Farma', 'storage' => 'suhu ruang', 'maxQty' => null],
        'PRD-012' => ['nie' => 'NA18211200089', 'composition' => 'Isopropyl alcohol 70%.', 'indication' => 'Membersihkan dan mendisinfeksi kulit sebelum penyuntikan atau pengambilan darah.', 'dosage' => 'Usap area kulit sekali pakai, biarkan kering sebelum tindakan.', 'sideEffects' => 'Iritasi pada kulit yang sangat sensitif.', 'warning' => null, 'manufacturer' => 'PT Griya Farma', 'storage' => 'suhu ruang', 'maxQty' => null],
    ];

    public function run(): void
    {
        $categories = $this->seedCategories();
        $suppliers = $this->seedSuppliers();
        $products = $this->seedProducts($categories, $suppliers);

        $this->seedStock($products);
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $categories = [];

        foreach (self::CATEGORIES as $position => [$name, $slug, $status, $description]) {
            $categories[$name] = Category::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'image_path' => "/media/images/categories/{$slug}.png",
                    'position' => $position,
                    'status' => $status,
                    'deleted_at' => null,
                ],
            );
        }

        return $categories;
    }

    /**
     * @return array<string, Supplier>
     */
    private function seedSuppliers(): array
    {
        $suppliers = [];

        foreach (self::SUPPLIERS as [$code, $name, $contact, $email, $phone, $licence, $kota, $address, $status, $joined]) {
            $suppliers[$name] = Supplier::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'contact_person' => $contact,
                    'email' => $email,
                    'phone' => $phone,
                    'license_number' => $licence,
                    'address_line' => $address,
                    'kota' => $kota,
                    'provinsi' => 'DKI Jakarta',
                    'payment_term_days' => 30,
                    'status' => $status,
                    'created_at' => Carbon::parse($joined),
                    'deleted_at' => null,
                ],
            );
        }

        return $suppliers;
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Supplier>  $suppliers
     * @return list<array{product: Product, stock: int}>
     */
    private function seedProducts(array $categories, array $suppliers): array
    {
        $products = [];

        foreach (self::PRODUCTS as [$sku, $name, $category, $supplier, $image, $price, $oldPrice, $stock, $sold, $rating, $unit, $prescription, $blurb]) {
            $pharma = self::PHARMA[$sku];

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category_id' => $categories[$category]->id,
                    'supplier_id' => $suppliers[$supplier]->id,
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'unit' => $unit,
                    'blurb' => $blurb,
                    'drug_class' => $pharma['drugClass'] ?? $this->drugClass($category, $prescription),
                    'nie_bpom' => $pharma['nie'],
                    'composition' => $pharma['composition'],
                    'indication' => $pharma['indication'],
                    'dosage' => $pharma['dosage'],
                    'side_effects' => $pharma['sideEffects'],
                    'warning' => $pharma['warning'],
                    'manufacturer' => $pharma['manufacturer'],
                    'max_qty_per_order' => $pharma['maxQty'],
                    'storage' => $pharma['storage'],
                    'requires_prescription' => $prescription,
                    'weight_grams' => 150,
                    'sold_count' => $sold,
                    'rating' => $rating,
                    'status' => 'aktif',
                    'deleted_at' => null,
                ],
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'position' => 0],
                [
                    'path' => "/media/images/product/p-{$image}.png",
                    'alt' => $name,
                    'is_primary' => true,
                ],
            );

            // PRD-001 shows the "several photos, reorderable" side of Fase 4.1
            // with real seed data rather than only through an admin upload.
            if ($sku === 'PRD-001') {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'position' => 1],
                    ['path' => '/media/images/product/p-13.png', 'alt' => $name, 'is_primary' => false],
                );
            }

            $products[] = ['product' => $product, 'stock' => $stock];
        }

        return $products;
    }

    /**
     * Bagikan total stok nasional ke seluruh cabang.
     *
     * Sebagian cabang sengaja dikosongkan supaya keadaan "ada di cabang ini,
     * habis di cabang sebelah" terlihat sejak data awal — itu justru inti model
     * multi-cabang, bukan kasus pinggiran. Jumlah keseluruhan tetap persis sama
     * dengan angka pada daftar produk di atas.
     *
     * @param  list<array{product: Product, stock: int}>  $products
     */
    private function seedStock(array $products): void
    {
        $branches = Branch::orderBy('id')->get();
        $count = $branches->count();

        if ($count === 0) {
            return;
        }

        foreach ($products as $index => ['product' => $product, 'stock' => $total]) {
            $shares = array_fill(0, $count, intdiv($total, $count));
            $shares[0] += $total % $count;

            foreach ($shares as $position => $share) {
                if ($position > 0 && ($index + $position) % 4 === 0) {
                    $shares[0] += $share;
                    $shares[$position] = 0;
                }
            }

            foreach ($branches as $position => $branch) {
                $quantity = $shares[$position];

                BranchStock::updateOrCreate(
                    ['branch_id' => $branch->id, 'product_id' => $product->id],
                    [
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'reorder_point' => 20,
                        'is_listed' => true,
                    ],
                );

                if ($quantity > 0) {
                    InventoryBatch::updateOrCreate(
                        [
                            'branch_id' => $branch->id,
                            'product_id' => $product->id,
                            'batch_number' => 'B'.now()->year.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                        ],
                        [
                            'expires_at' => now()->addMonths(6 + ($index % 18)),
                            'quantity' => $quantity,
                            'received_at' => now()->subDays(30),
                        ],
                    );
                }
            }
        }
    }

    private function drugClass(string $category, bool $prescription): string
    {
        if ($prescription) {
            return 'keras';
        }

        return match ($category) {
            'Obat Bebas', 'Vitamin & Suplemen', 'Kebutuhan Keluarga', 'Obat Tradisional' => 'bebas',
            default => 'non-obat',
        };
    }
}

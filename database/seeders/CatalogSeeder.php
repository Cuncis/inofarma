<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\Catalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Memindahkan katalog contoh dari `App\Support\Catalog` ke basis data.
 *
 * Sengaja memakai sumber yang sama dengan prototipe supaya tampilan tidak
 * berubah saat penyimpanan berpindah dari session ke basis data. Setelah
 * Fase 1.4 selesai, `App\Support\Catalog` dihapus dan seeder ini menyimpan
 * datanya sendiri.
 */
class CatalogSeeder extends Seeder
{
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

        foreach (Catalog::categories() as $index => $seed) {
            $categories[$seed['name']] = Category::updateOrCreate(
                ['slug' => $seed['slug']],
                [
                    'name' => $seed['name'],
                    'description' => $seed['description'],
                    'position' => $index,
                    'status' => $seed['status'] === 'Aktif' ? 'aktif' : 'nonaktif',
                ],
            );
        }

        return $categories;
    }

    /**
     * Penjual template menjadi pemasok — di jaringan milik sendiri, pihak luar
     * memasok, bukan menjual.
     *
     * @return array<string, Supplier>
     */
    private function seedSuppliers(): array
    {
        $seeds = [
            ['PBF-001', 'PT Kimia Farma Trading', 'Jakarta Pusat', 'DKI Jakarta'],
            ['PBF-002', 'PT Kalbe Farma', 'Jakarta Timur', 'DKI Jakarta'],
            ['PBF-003', 'PT Dexa Medica', 'Tangerang', 'Banten'],
            ['PBF-004', 'PT Bio Farma', 'Bandung', 'Jawa Barat'],
        ];

        $suppliers = [];

        foreach ($seeds as $index => [$code, $name, $kota, $provinsi]) {
            $suppliers[$name] = Supplier::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'contact_person' => 'Bagian Penjualan',
                    'phone' => '+62 21 5000 '.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'license_number' => 'PBF/2024/'.str_pad((string) ($index + 101), 5, '0', STR_PAD_LEFT),
                    'kota' => $kota,
                    'provinsi' => $provinsi,
                    'payment_term_days' => 30,
                    'status' => 'aktif',
                ],
            );
        }

        return $suppliers;
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Supplier>  $suppliers
     * @return list<Product>
     */
    private function seedProducts(array $categories, array $suppliers): array
    {
        $supplierList = array_values($suppliers);
        $products = [];

        foreach (Catalog::products() as $index => $seed) {
            $products[] = Product::updateOrCreate(
                ['sku' => $seed['id']],
                [
                    'name' => $seed['name'],
                    'slug' => Str::slug($seed['name']),
                    'category_id' => $categories[$seed['category']]->id,
                    'supplier_id' => $supplierList[$index % count($supplierList)]->id,
                    'price' => $seed['price'],
                    'old_price' => $seed['oldPrice'],
                    'unit' => $seed['unit'],
                    'blurb' => $seed['blurb'],
                    'drug_class' => $this->drugClass($seed['category'], $seed['prescription']),
                    'requires_prescription' => $seed['prescription'],
                    'weight_grams' => 150,
                    'sold_count' => $seed['sold'],
                    'rating' => $seed['rating'],
                    'status' => 'aktif',
                ],
            );
        }

        return $products;
    }

    /**
     * Sebar stok ke seluruh cabang dengan jumlah yang berbeda-beda, supaya
     * "tersedia di cabang A, habis di cabang B" bisa terlihat sejak awal.
     *
     * @param  list<Product>  $products
     */
    private function seedStock(array $products): void
    {
        $branches = Branch::orderBy('id')->get();

        foreach ($products as $productIndex => $product) {
            foreach ($branches as $branchIndex => $branch) {
                // Pola sederhana dan dapat diulang: sebagian cabang sengaja kosong.
                $quantity = (($productIndex + $branchIndex) % 4 === 0)
                    ? 0
                    : (($productIndex * 7 + $branchIndex * 13) % 90) + 10;

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
                            'batch_number' => 'B'.now()->year.str_pad((string) ($productIndex + 1), 3, '0', STR_PAD_LEFT),
                        ],
                        [
                            'expires_at' => now()->addMonths(6 + ($productIndex % 18)),
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
            'Obat Bebas' => 'bebas',
            'Obat Keras' => 'keras',
            'Suplemen', 'Antiseptik' => 'bebas',
            default => 'non-obat',
        };
    }
}

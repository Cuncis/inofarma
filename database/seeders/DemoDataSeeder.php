<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Kembalikan data contoh ke keadaan awal.
 *
 * Ini yang dijalankan tombol "Reset" di layar admin. Cabang **tidak** ikut
 * dihapus — sepuluh cabang itu data sungguhan, bukan contoh.
 *
 * Penghapusan memakai query builder, bukan Eloquent, supaya baris yang sudah
 * di-soft-delete ikut hilang; menyisakannya akan menabrak indeks unik saat
 * penyemaian ulang.
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Urutan penghapusan mengikuti kunci asing: anak lebih dulu, induk terakhir.
     *
     * @var list<string>
     */
    private const TABLES = [
        'order_items',
        'orders',
        'customer_addresses',
        'customers',
        'inventory_movements',
        'inventory_batches',
        'branch_stocks',
        'product_images',
        'products',
        'suppliers',
        'categories',
    ];

    public function run(): void
    {
        foreach (self::TABLES as $table) {
            DB::table($table)->delete();
        }

        $this->call([
            CatalogSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}

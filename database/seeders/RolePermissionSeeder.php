<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the five roles the roadmap names and the permission catalogue behind
 * them. `branch_id` is not set here — that lives on the `User` row itself
 * (Fase 3.2), since the same role ("Kasir") is granted to staff at every
 * branch; it's the user, not the role, that is scoped.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Roles created above may already have cached an empty permission
        // list — the registrar caches on first read, not per-write, so a
        // sync immediately after a bulk create needs an explicit flush.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('Super Admin', 'web')
            ->syncPermissions(PermissionCatalog::all());

        Role::findOrCreate('Manajer Area', 'web')->syncPermissions([
            'Produk:Lihat', 'Produk:Tambah', 'Produk:Ubah',
            'Pesanan:Lihat', 'Pesanan:Proses', 'Pesanan:Batalkan', 'Pesanan:Refund',
            'Pelanggan:Lihat', 'Pelanggan:Tambah', 'Pelanggan:Ubah',
            'Inventaris:Lihat', 'Inventaris:Sesuaikan Stok', 'Inventaris:Terima Barang',
            'Cabang:Lihat', 'Cabang:Ubah',
            'Laporan:Lihat', 'Laporan:Ekspor',
        ]);

        Role::findOrCreate('APJ Cabang', 'web')->syncPermissions([
            'Produk:Lihat', 'Produk:Tambah', 'Produk:Ubah',
            'Pesanan:Lihat', 'Pesanan:Proses', 'Pesanan:Batalkan',
            'Pelanggan:Lihat',
            'Inventaris:Lihat', 'Inventaris:Sesuaikan Stok', 'Inventaris:Terima Barang',
            'Cabang:Lihat',
            'Laporan:Lihat',
        ]);

        Role::findOrCreate('Kasir', 'web')->syncPermissions([
            'Pesanan:Lihat', 'Pesanan:Proses',
            'Pelanggan:Lihat', 'Pelanggan:Tambah',
            'Inventaris:Lihat',
        ]);

        Role::findOrCreate('Staf Gudang', 'web')->syncPermissions([
            'Inventaris:Lihat', 'Inventaris:Sesuaikan Stok', 'Inventaris:Terima Barang',
            'Cabang:Lihat',
        ]);
    }
}

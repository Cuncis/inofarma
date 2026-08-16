<?php

namespace App\Support;

/**
 * The single list of permissions the admin's Peran/Hak Akses screens render
 * and `RolePermissionSeeder` seeds — one flat "Module:Ability" string per
 * permission, matching how `RoleController`/`Permissions.jsx` key their grid.
 */
class PermissionCatalog
{
    /**
     * @var array<string, list<string>>
     */
    public const GROUPS = [
        'Produk' => ['Lihat', 'Tambah', 'Ubah', 'Hapus'],
        'Pesanan' => ['Lihat', 'Proses', 'Batalkan', 'Refund'],
        'Pelanggan' => ['Lihat', 'Tambah', 'Ubah', 'Hapus'],
        'Inventaris' => ['Lihat', 'Sesuaikan Stok', 'Terima Barang'],
        'Cabang' => ['Lihat', 'Tambah', 'Ubah', 'Hapus'],
        'Laporan' => ['Lihat', 'Ekspor'],
        'Pengaturan' => ['Lihat', 'Ubah'],
        'Peran' => ['Lihat', 'Ubah'],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::GROUPS as $module => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = "{$module}:{$ability}";
            }
        }

        return $permissions;
    }
}

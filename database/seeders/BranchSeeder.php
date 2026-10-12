<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Support\CodeSequence;
use App\Support\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Empat cabang Apotek Inofarma: Jengki, Kalisari, Kayu Manis, dan Pisangan Lama.
 *
 * Alamat jalan dan koordinatnya belum ada, jadi yang tersimpan hanyalah nama,
 * wilayah yang cukup pasti, dan alamat sementara. Lengkapi lewat halaman Ubah
 * Cabang di admin. Selama koordinat kosong, cabang ini dilewati saat mengurutkan
 * "cabang terdekat" dan menghitung radius pengantaran.
 *
 * Untuk saat ini hanya Pisangan Lama yang mengantar pesanan; tiga cabang lain
 * hanya tempat pengambilan (`supports_delivery` false, bisa diaktifkan lagi di
 * admin).
 *
 * Aman dijalankan ulang, termasuk di produksi: cabang dikenali dari namanya dan
 * hanya DIBUAT bila belum ada, tidak pernah menimpa data yang sudah dikoreksi di
 * admin dan tidak menghapus cabang lain. Kode cabang baru melanjutkan urutan yang
 * ada (CB-001 di database kosong, CB-011 bila CB-010 sudah terpakai).
 */
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            'senin' => ['open' => '08:00', 'close' => '21:00'],
            'selasa' => ['open' => '08:00', 'close' => '21:00'],
            'rabu' => ['open' => '08:00', 'close' => '21:00'],
            'kamis' => ['open' => '08:00', 'close' => '21:00'],
            'jumat' => ['open' => '08:00', 'close' => '21:00'],
            'sabtu' => ['open' => '08:00', 'close' => '21:00'],
            'minggu' => ['open' => '09:00', 'close' => '20:00'],
        ];

        foreach ($this->branches() as $branch) {
            if (Branch::withTrashed()->where('name', $branch['name'])->exists()) {
                continue;
            }

            Branch::create([
                ...collect($branch)->except('supports_delivery')->all(),
                'code' => CodeSequence::next(Branch::withTrashed(), 'code', 'CB-'),
                'slug' => Slug::unique(Branch::withTrashed(), Str::after($branch['name'], 'Apotek Inofarma ')),
                'operating_hours' => $hours,
                'supports_delivery' => $branch['supports_delivery'] ?? true,
                'supports_pickup' => true,
                'delivery_radius_km' => 10,
                'status' => 'aktif',
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function branches(): array
    {
        return [
            [
                'name' => 'Apotek Inofarma Jengki',
                'supports_delivery' => false,
                'address_line' => 'Jl. Jengki No.28, RT.8/RW.4',
                'kelurahan' => 'Kebon Pala',
                'kecamatan' => 'Makasar',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13650',
            ],
            [
                'name' => 'Apotek Inofarma Kalisari',
                'supports_delivery' => false,
                'address_line' => 'Jl. Kalisari III Nomor 69, RT 003/RW 03',
                'kelurahan' => 'Kalisari',
                'kecamatan' => 'Pasar Rebo',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13790',
            ],
            [
                'name' => 'Apotek Inofarma Kayu Manis',
                'supports_delivery' => false,
                'address_line' => 'Jalan Kayu Manis, RT 006/RW 05',
                'kelurahan' => 'Balekambang',
                'kecamatan' => 'Kramatjati',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13530',
            ],
            [
                'name' => 'Apotek Inofarma Pisangan Lama',
                'address_line' => 'Jalan Pisangan Lama III No.2, RT.8/RW.4',
                'kelurahan' => 'Pisangan Timur',
                'kecamatan' => 'Pulo Gadung',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13230',
                'phone' => '081214018964',
                'whatsapp' => '081214018964',
            ],
        ];
    }
}

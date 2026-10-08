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
                ...$branch,
                'code' => CodeSequence::next(Branch::withTrashed(), 'code', 'CB-'),
                'slug' => Slug::unique(Branch::withTrashed(), Str::after($branch['name'], 'Apotek Inofarma ')),
                'operating_hours' => $hours,
                'supports_delivery' => true,
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
                'address_line' => 'Alamat belum diisi (Jengki)',
                'kelurahan' => null,
                'kecamatan' => null,
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'name' => 'Apotek Inofarma Kalisari',
                'address_line' => 'Alamat belum diisi (Kalisari)',
                'kelurahan' => 'Kalisari',
                'kecamatan' => 'Pasar Rebo',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'name' => 'Apotek Inofarma Kayu Manis',
                'address_line' => 'Alamat belum diisi (Kayu Manis)',
                'kelurahan' => 'Kayu Manis',
                'kecamatan' => 'Matraman',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'name' => 'Apotek Inofarma Pisangan Lama',
                'address_line' => 'Alamat belum diisi (Pisangan Lama)',
                'kelurahan' => 'Pisangan Lama',
                'kecamatan' => 'Pulo Gadung',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
            ],
        ];
    }
}

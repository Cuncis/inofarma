<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sepuluh cabang Jabodetabek yang beroperasi saat ini.
 *
 * KOORDINAT MASIH KOSONG. Seluruh fitur "cabang terdekat" bergantung padanya,
 * jadi isi manual dari Google Maps sebelum Fase 2.3 — satu koordinat salah
 * berarti pelanggan diarahkan ke cabang yang jauh.
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

        foreach ($this->branches() as $index => $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                [
                    ...$branch,
                    'slug' => Str::slug(Str::after($branch['name'], 'Apotek Inofarma ')),
                    'operating_hours' => $hours,
                    'supports_delivery' => true,
                    'supports_pickup' => true,
                    'delivery_radius_km' => 10,
                    'status' => 'aktif',
                ],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function branches(): array
    {
        return [
            [
                'code' => 'CB-001',
                'name' => 'Apotek Inofarma Kapten Yusuf',
                'address_line' => 'Jl. Kapten Yusuf RT 001 RW 007',
                'kelurahan' => 'Cikaret',
                'kecamatan' => 'Bogor Selatan',
                'kota' => 'Kota Bogor',
                'provinsi' => 'Jawa Barat',
                'postal_code' => null,
            ],
            [
                'code' => 'CB-002',
                'name' => 'Apotek Inofarma Otista',
                'address_line' => 'Jl. Otista Raya No.27A, Ciputat',
                'kelurahan' => null,
                'kecamatan' => 'Ciputat',
                'kota' => 'Kota Tangerang Selatan',
                'provinsi' => 'Banten',
                'postal_code' => '15411',
            ],
            [
                'code' => 'CB-003',
                'name' => 'Apotek Inofarma Darul Fallah',
                'address_line' => 'Jl. Mesjid Darul Falah No.27, RT.4/RW.2',
                'kelurahan' => 'Petukangan Utara',
                'kecamatan' => 'Pesanggrahan',
                'kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '12260',
            ],
            [
                'code' => 'CB-004',
                'name' => 'Apotek Inofarma Parakan',
                'address_line' => 'Jl. Parakan No.101e, RT.1/RW.1',
                'kelurahan' => 'Pd. Benda',
                'kecamatan' => 'Pamulang',
                'kota' => 'Kota Tangerang Selatan',
                'provinsi' => 'Banten',
                'postal_code' => '15416',
            ],
            [
                'code' => 'CB-005',
                'name' => 'Apotek Inofarma Syahdan',
                'address_line' => 'Jl. Kyai H. Syahdan No.2, RT.2/RW.11',
                'kelurahan' => 'Palmerah',
                'kecamatan' => 'Palmerah',
                'kota' => 'Jakarta Barat',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '11480',
            ],
            [
                'code' => 'CB-006',
                'name' => 'Apotek Inofarma Taruna Jaya',
                'address_line' => 'Jl. Taruna Jaya 4 No.6, RT.7/RW.5',
                'kelurahan' => 'Cibubur',
                'kecamatan' => 'Ciracas',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13720',
            ],
            [
                'code' => 'CB-007',
                'name' => 'Apotek Inofarma Keamanan',
                'address_line' => 'Jl. Keamanan No.59, RT.001/RW.007',
                'kelurahan' => 'Keagungan',
                'kecamatan' => 'Taman Sari',
                'kota' => 'Jakarta Barat',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '11130',
            ],
            [
                'code' => 'CB-008',
                'name' => 'Apotek Inofarma Pulo Gebang',
                'address_line' => 'Jl. Raya Pulo Gebang No.21, RT.1/RW.6',
                'kelurahan' => 'Pulo Gebang',
                'kecamatan' => 'Cakung',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '13950',
            ],
            [
                'code' => 'CB-009',
                'name' => 'Apotek Inofarma Duri Kepa',
                'address_line' => 'Jl. Duri Raya No.5, RT.6/RW.1',
                'kelurahan' => 'Duri Kepa',
                'kecamatan' => 'Kebon Jeruk',
                'kota' => 'Jakarta Barat',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '11510',
            ],
            [
                'code' => 'CB-010',
                'name' => 'Apotek Inofarma Kebagusan',
                'address_line' => 'Jl. Kebagusan Raya, RT.7/RW.6',
                'kelurahan' => 'Kebagusan',
                'kecamatan' => 'Pasar Minggu',
                'kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'postal_code' => '12520',
            ],
        ];
    }
}

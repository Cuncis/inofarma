<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sepuluh cabang Jabodetabek yang beroperasi saat ini.
 *
 * Koordinat diisi dari pencarian OpenStreetMap/Nominatim pada tingkat
 * jalan atau kelurahan — cukup akurat untuk mengurutkan "cabang terdekat" dan
 * menghitung radius pengantaran, tapi belum diverifikasi manual terhadap lokasi
 * gerai yang sesungguhnya. Perbaiki lewat `php artisan cabang:geocode --code=
 * CB-00X --lat=... --lng=...` begitu koordinat pasti tersedia (mis. dari plus
 * code Google Maps gerai), atau langsung lewat halaman Ubah Cabang di admin.
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
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                [
                    ...$branch,
                    'slug' => Str::slug(Str::after($branch['name'], 'Apotek Inofarma ')),
                    'maps_url' => "https://www.google.com/maps?q={$branch['latitude']},{$branch['longitude']}",
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
                'latitude' => -6.6140010,
                'longitude' => 106.7881897,
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
                'latitude' => -6.3253490,
                'longitude' => 106.7424423,
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
                'latitude' => -6.2300814,
                'longitude' => 106.7530005,
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
                'latitude' => -6.3405289,
                'longitude' => 106.7100816,
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
                'latitude' => -6.2005849,
                'longitude' => 106.7861345,
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
                'latitude' => -6.3611927,
                'longitude' => 106.8846432,
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
                'latitude' => -6.1517789,
                'longitude' => 106.8157163,
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
                'latitude' => -6.1994676,
                'longitude' => 106.9547933,
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
                'latitude' => -6.1662147,
                'longitude' => 106.7678455,
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
                'latitude' => -6.3113488,
                'longitude' => 106.8261007,
            ],
        ];
    }
}

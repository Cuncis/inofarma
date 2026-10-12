<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real addresses for the three pickup branches. Only branches still on the
     * "Alamat belum diisi" placeholder are touched, so an address corrected in
     * the admin is never overwritten.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        $addresses = [
            'Apotek Inofarma Jengki' => ['Jl. Jengki No.28, RT.8/RW.4', 'Kebon Pala', 'Makasar', '13650'],
            'Apotek Inofarma Kalisari' => ['Jl. Kalisari III Nomor 69, RT 003/RW 03', 'Kalisari', 'Pasar Rebo', '13790'],
            'Apotek Inofarma Kayu Manis' => ['Jalan Kayu Manis, RT 006/RW 05', 'Balekambang', 'Kramatjati', '13530'],
        ];

        foreach ($addresses as $name => [$line, $kelurahan, $kecamatan, $postalCode]) {
            DB::table('branches')
                ->where('name', $name)
                ->where('address_line', 'like', 'Alamat belum diisi%')
                ->update([
                    'address_line' => $line,
                    'kelurahan' => $kelurahan,
                    'kecamatan' => $kecamatan,
                    'kota' => 'Jakarta Timur',
                    'provinsi' => 'DKI Jakarta',
                    'postal_code' => $postalCode,
                ]);
        }
    }

    public function down(): void {}
};

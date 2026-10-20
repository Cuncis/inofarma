<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each apotek gets its own page on the storefront (`/apotek/{slug}`): a photo,
 * and the pharmacist's practice hours and the shop hours as written on the
 * licence board ("Senin - Jumat (08.00-16.00)"), which plain open/close times
 * cannot express ("Shift").
 *
 * The four real apotek are filled in here, matched by name, together with
 * their SIA, SIPA and pharmacist. Pisangan Lama's slug also loses its
 * "apotek-inofarma-" prefix so every page reads `/apotek/<place>`.
 */
return new class extends Migration
{
    private const PROFILES = [
        'Apotek Inofarma Jengki' => [
            'slug' => 'jengki',
            'photo_path' => '/media/images/apotek/jengki.png',
            'sia_number' => '30092400346910003',
            'apj_sipa_number' => '8/B.19/31.75.08.1003.39.K-2/3/TM.09.19/e/2025',
            'apj_name' => 'apt. Yulita Fitri Rosanti, S.Farm',
            'practice_schedule' => 'Senin - Jumat (08.00-16.00)',
            'store_schedule' => 'Senin - Jumat (08.00-16.00)',
        ],
        'Apotek Inofarma Kayu Manis' => [
            'slug' => 'kayu-manis',
            'photo_path' => '/media/images/apotek/kayu-manis.png',
            'sia_number' => '30092400346910004',
            'apj_sipa_number' => '15/B.19/31.75.04.1005.38.R-1/3/TM.09.19/e/2025',
            'apj_name' => 'apt. Nadia Tria Nurchasanah, S.Farm',
            'practice_schedule' => 'Senin - Jumat (08.00-21.00) Shift',
            'store_schedule' => 'Senin - Jumat (08.00-21.00) Shift',
        ],
        'Apotek Inofarma Pisangan Lama' => [
            'slug' => 'pisangan-lama',
            'photo_path' => '/media/images/apotek/pisangan-lama.png',
            'sia_number' => '30092400346910006',
            'apj_sipa_number' => '13/B.19/31.75.02.1002.42.K-3.b/3/TM.09.19/e/2025',
            'apj_name' => 'apt. Fitria Ayuni, S.Farm',
            'practice_schedule' => 'Senin - Jumat (08.00-17.00)',
            'store_schedule' => 'Senin - Jumat (08.00-17.00)',
        ],
        'Apotek Inofarma Kalisari' => [
            'slug' => 'kalisari',
            'photo_path' => '/media/images/apotek/kalisari.png',
            'sia_number' => '30092400346910005',
            'apj_sipa_number' => '1/B.19/31.75.05.1004.41.R-1/4/TM.09/e/2025',
            'apj_name' => 'apt. Naomy Octavinna, S.Farm',
            'practice_schedule' => 'Senin - Sabtu (08.00-15.00)',
            'store_schedule' => 'Senin - Sabtu (08.00-15.00)',
        ],
    ];

    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('maps_url');
            $table->string('practice_schedule', 120)->nullable()->after('apj_sipa_number');
            $table->string('store_schedule', 120)->nullable()->after('practice_schedule');
        });

        foreach (self::PROFILES as $name => $profile) {
            $slugTaken = DB::table('branches')
                ->where('slug', $profile['slug'])
                ->where('name', '!=', $name)
                ->exists();

            DB::table('branches')
                ->where('name', $name)
                ->update($slugTaken ? array_diff_key($profile, ['slug' => true]) : $profile);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'practice_schedule', 'store_schedule']);
        });
    }
};

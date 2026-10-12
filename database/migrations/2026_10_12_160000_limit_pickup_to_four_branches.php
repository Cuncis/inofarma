<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PICKUP_BRANCHES = [
        'Apotek Inofarma Pisangan Lama',
        'Apotek Inofarma Jengki',
        'Apotek Inofarma Kayu Manis',
        'Apotek Inofarma Kalisari',
    ];

    private const DELIVERY_BRANCH = 'Apotek Inofarma Pisangan Lama';

    /**
     * Every product ships from Pisangan Lama, and "Ambil di toko" offers only
     * the four pickup points. Both are per-branch switches in Cabang
     * ("Melayani antar", "Melayani ambil di toko"), so any other branch is just
     * switched off for both and can be switched back on there.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        DB::table('branches')->update([
            'supports_pickup' => DB::raw('name IN ('.$this->quoted(self::PICKUP_BRANCHES).')'),
            'supports_delivery' => DB::raw('name = '.DB::getPdo()->quote(self::DELIVERY_BRANCH)),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        DB::table('branches')->update(['supports_pickup' => true]);
    }

    /** @param  list<string>  $names */
    private function quoted(array $names): string
    {
        return collect($names)->map(fn (string $name) => DB::getPdo()->quote($name))->implode(', ');
    }
};

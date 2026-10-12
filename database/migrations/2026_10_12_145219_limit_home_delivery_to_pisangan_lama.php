<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OTHER_BRANCHES = [
        'Apotek Inofarma Jengki',
        'Apotek Inofarma Kalisari',
        'Apotek Inofarma Kayu Manis',
    ];

    /**
     * For now, orders are delivered only from Apotek Inofarma Pisangan Lama; the
     * other three branches are pickup points. Delivery is a per-branch switch
     * (`supports_delivery`, "Melayani antar" in Cabang), so this just turns it
     * off for them. Turn it back on there when a branch starts delivering.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        DB::table('branches')->whereIn('name', self::OTHER_BRANCHES)->update(['supports_delivery' => false]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        DB::table('branches')->whereIn('name', self::OTHER_BRANCHES)->update(['supports_delivery' => true]);
    }
};

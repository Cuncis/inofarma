<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Pisangan Lama is the shipping origin, so Biteship needs its phone. Only fills blanks. */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        $branch = DB::table('branches')->where('name', 'Apotek Inofarma Pisangan Lama');

        (clone $branch)->where(fn ($q) => $q->whereNull('phone')->orWhere('phone', ''))->update(['phone' => '081214018964']);
        (clone $branch)->where(fn ($q) => $q->whereNull('whatsapp')->orWhere('whatsapp', ''))->update(['whatsapp' => '081214018964']);
    }

    public function down(): void {}
};

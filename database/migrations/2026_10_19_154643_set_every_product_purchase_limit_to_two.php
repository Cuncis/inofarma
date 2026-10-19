<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A product's own max_qty_per_order is now its real limit instead of only
     * being able to lower the shared cap of 2, so every product starts at 2.
     * Products already limited to 1 keep it, so none gets a looser limit than
     * before. Staff change them afterwards from the product list or the import.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')
            ->where(fn ($query) => $query->whereNull('max_qty_per_order')->orWhere('max_qty_per_order', '!=', 1))
            ->update(['max_qty_per_order' => 2]);
    }

    public function down(): void {}
};

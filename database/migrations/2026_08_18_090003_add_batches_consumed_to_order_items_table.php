<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manifest batch FEFO yang dipakai `StockAllocator::consume()` saat pesanan
 * dibuat — bentuknya sama dengan `stock_transfers.batches_shipped`. Dipakai
 * untuk mengembalikan stok ke batch yang tepat bila pesanan dibatalkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('batches_consumed')->nullable()->after('line_total');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('batches_consumed');
        });
    }
};

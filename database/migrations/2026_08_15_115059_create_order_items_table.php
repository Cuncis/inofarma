<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baris pesanan.
 *
 * `product_name` dan `unit_price` sengaja disalin, bukan hanya `product_id`.
 * Sebuah pesanan harus tetap menunjukkan apa yang benar-benar dibeli dan
 * berapa harganya saat itu, walau produknya kemudian diganti nama, diubah
 * harganya, atau dihapus dari katalog.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()
                ->constrained()->nullOnDelete();

            // Snapshot — bukan turunan dari relasi di atas.
            $table->string('product_name');
            $table->string('sku', 40)->nullable();
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');

            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

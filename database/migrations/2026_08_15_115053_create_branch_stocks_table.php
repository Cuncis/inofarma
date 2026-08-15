<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stok satu produk di satu cabang.
 *
 * Inti dari model multi-cabang. Stok yang bisa dijual adalah
 * `quantity - reserved_quantity`: barang yang sudah dipesan tapi belum
 * diserahkan tidak boleh terjual dua kali.
 *
 * `price_override` memungkinkan cabang punya harga sendiri tanpa memaksa
 * menyunting ribuan baris saat harga nasional berubah — biarkan null untuk
 * mengikuti harga produk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('reorder_point')->default(0);
            $table->unsignedBigInteger('price_override')->nullable();
            $table->boolean('is_listed')->default(true);

            $table->timestamps();

            // Satu produk hanya boleh punya satu baris stok per cabang.
            $table->unique(['branch_id', 'product_id']);

            // "Cabang mana yang punya produk ini?" — kueri terpanas di etalase.
            $table->index(['product_id', 'is_listed', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};

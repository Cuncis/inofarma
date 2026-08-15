<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buku besar pergerakan stok.
 *
 * Stok tidak pernah diubah begitu saja — setiap perubahan meninggalkan satu
 * baris di sini dengan alasannya. Untuk apotek ini bukan kemewahan: saat ada
 * selisih stok, inilah satu-satunya cara menelusuri apa yang terjadi.
 *
 * `quantity` bertanda: positif menambah, negatif mengurangi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('inventory_batch_id')->nullable()
                ->constrained()->nullOnDelete();

            $table->enum('type', [
                'pembelian',      // barang masuk dari pemasok
                'penjualan',      // keluar karena pesanan
                'retur masuk',    // pelanggan mengembalikan
                'retur keluar',   // dikembalikan ke pemasok
                'transfer masuk', // dari cabang lain
                'transfer keluar',
                'penyesuaian',    // stok opname
                'kedaluwarsa',    // dimusnahkan
                'rusak',
            ]);

            $table->integer('quantity');
            $table->unsignedInteger('balance_after')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();

            // Siapa yang melakukan — null bila dilakukan sistem.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['branch_id', 'product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};

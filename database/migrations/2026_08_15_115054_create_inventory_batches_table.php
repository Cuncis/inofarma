<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch stok per cabang, dengan tanggal kedaluwarsa.
 *
 * Obat tidak bisa diperlakukan sebagai satu angka: dua kotak Paracetamol yang
 * sama bisa kedaluwarsa pada bulan berbeda. Pengambilan barang memakai FEFO
 * (First Expired, First Out) — index di bawah mendukung urutan itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('batch_number', 60);
            $table->date('expires_at');
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('cost_price')->nullable();
            $table->date('received_at')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'product_id', 'batch_number']);

            // Urutan pengambilan FEFO: yang paling cepat kedaluwarsa lebih dulu.
            $table->index(['branch_id', 'product_id', 'expires_at']);

            // Untuk peringatan "mendekati kedaluwarsa" di dasbor.
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};

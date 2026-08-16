<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perpindahan stok satu produk dari satu cabang ke cabang lain.
 *
 * Alurnya tiga langkah — diminta, dikirim, diterima — dan setiap langkah yang
 * benar-benar menggerakkan barang (dikirim, diterima) meninggalkan jejak di
 * `inventory_movements` pada kedua sisi. Barang dianggap "di jalan" antara
 * dikirim dan diterima: sudah berkurang dari cabang asal, belum bertambah di
 * cabang tujuan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();

            $table->foreignId('from_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->enum('status', ['diminta', 'dikirim', 'diterima', 'dibatalkan'])
                ->default('diminta');

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();

            // Which batches (and expiry dates) were actually picked when this
            // shipped — the receiving side re-creates those same batches at the
            // destination rather than losing their expiry at the border.
            $table->json('batches_shipped')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['from_branch_id', 'status']);
            $table->index(['to_branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};

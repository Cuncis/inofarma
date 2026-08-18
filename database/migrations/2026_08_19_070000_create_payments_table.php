<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu percobaan pembayaran ke gateway (DOKU) untuk satu `Order`.
 *
 * Sengaja bukan kolom di `orders` — satu pesanan bisa punya lebih dari satu
 * percobaan (sesi checkout DOKU kedaluwarsa lalu pelanggan mencoba lagi), dan
 * setiap percobaan butuh `invoice_number` sendiri karena DOKU mensyaratkannya
 * unik per permintaan. `orders.payment_status` tetap satu-satunya kebenaran
 * yang dibaca layar lain (Faktur, dsb) — tabel ini adalah jejak gateway di
 * baliknya, bukan pengganti kolom itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();

            $table->string('gateway', 20)->default('doku');
            $table->string('invoice_number', 80)->unique();
            $table->enum('status', ['pending', 'success', 'failed', 'expired', 'refunded'])
                ->default('pending');
            $table->unsignedBigInteger('amount');

            // Detail sisi DOKU, untuk lacak jejak dan Check Status API.
            $table->string('request_id', 128)->nullable();
            $table->string('token_id')->nullable();
            $table->string('channel', 60)->nullable();
            $table->text('checkout_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_note')->nullable();

            $table->json('raw_response')->nullable();
            $table->json('raw_notification')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

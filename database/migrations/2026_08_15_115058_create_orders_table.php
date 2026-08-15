<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan.
 *
 * Selalu berasal dari SATU cabang — keranjang campur-cabang tidak diizinkan,
 * karena satu pesanan yang dikirim dari dua tempat rumit dan membingungkan.
 *
 * `fulfilment` menentukan apakah barang diantar atau diambil sendiri; hampir
 * setiap aturan setelah checkout bercabang di kolom ini.
 *
 * Angka uang disalin ke sini saat pesanan dibuat dan tidak pernah dihitung
 * ulang dari data hidup — pesanan adalah catatan sejarah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();

            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();

            $table->enum('fulfilment', ['antar', 'ambil']);
            $table->enum('status', [
                'menunggu pembayaran',
                'diproses',
                'siap diambil',
                'dikirim',
                'selesai',
                'dibatalkan',
                'kedaluwarsa',
            ])->default('menunggu pembayaran');

            $table->string('payment_method', 40)->nullable();
            $table->enum('payment_status', ['belum bayar', 'lunas', 'gagal', 'refund'])
                ->default('belum bayar');

            // Snapshot uang, dalam rupiah penuh.
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);

            // Snapshot alamat: alamat pelanggan boleh berubah, pesanan tidak.
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('shipping_latitude', 10, 7)->nullable();
            $table->decimal('shipping_longitude', 10, 7)->nullable();

            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Batas waktu bayar / ambil; scheduler memakainya untuk melepas stok.
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['status', 'expires_at']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

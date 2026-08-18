<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keranjang pelanggan yang sudah masuk. Tamu punya keranjangnya sendiri di
 * *session* (lihat `App\Support\Cart\CartManager`) dan digabung ke sini saat
 * masuk — tabel ini tidak pernah menyimpan keranjang tamu.
 *
 * Satu pelanggan hanya punya satu keranjang, dan keranjang itu terikat pada
 * satu cabang (lihat 3.3 di ROADMAP.md): mencampur cabang dalam satu
 * keranjang berarti satu pesanan dikirim dari dua tempat.
 *
 * Alamat yang dipilih saat checkout disimpan di sini juga (`customer_address_id`)
 * karena memilihnya adalah kunjungan halaman penuh yang terpisah
 * (`Shop/ShippingDetails`) — cara terima dan metode bayar dipilih langsung di
 * halaman checkout dan dikirim bersama saat pesanan dibuat, jadi tidak perlu
 * kolom tersendiri untuk itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

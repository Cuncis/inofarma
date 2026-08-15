<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat pengiriman pelanggan.
 *
 * Menyimpan koordinat, bukan hanya teks: tanpa itu tidak ada cara menghitung
 * jarak ke cabang atau memeriksa apakah alamat masuk radius antar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('label', 40)->default('Rumah');
            $table->string('recipient_name');
            $table->string('phone', 30);

            $table->string('address_line');
            $table->string('kelurahan', 80)->nullable();
            $table->string('kecamatan', 80)->nullable();
            $table->string('kota', 80);
            $table->string('provinsi', 80);
            $table->string('postal_code', 10)->nullable();
            $table->string('note')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};

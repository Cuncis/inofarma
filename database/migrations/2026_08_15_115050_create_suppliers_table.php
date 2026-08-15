<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemasok — pedagang besar farmasi (PBF) atau distributor.
 *
 * Menggantikan konsep "Penjual" dari template, yang mengasumsikan marketplace.
 * Di jaringan milik sendiri, pihak eksternal adalah yang memasok, bukan menjual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            // Nomor izin PBF — wajib untuk memasok obat secara legal.
            $table->string('license_number', 60)->nullable()->unique();

            $table->string('address_line')->nullable();
            $table->string('kota', 80)->nullable();
            $table->string('provinsi', 80)->nullable();
            $table->unsignedSmallInteger('payment_term_days')->default(30);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

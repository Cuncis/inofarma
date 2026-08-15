<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cabang apotek.
 *
 * Setiap baris adalah satu apotek fisik dengan izin (SIA) dan apoteker
 * penanggung jawab (APJ) sendiri. Koordinat wajib diisi karena seluruh fitur
 * "cabang terdekat" bergantung padanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->string('address_line');
            $table->string('kelurahan', 80)->nullable();
            $table->string('kecamatan', 80)->nullable();
            $table->string('kota', 80);
            $table->string('provinsi', 80);
            $table->string('postal_code', 10)->nullable();

            // Disimpan terpisah agar bisa diindeks; presisi 7 desimal ≈ 1 cm.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('maps_url')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();

            // Izin: satu cabang, satu SIA, satu APJ.
            $table->string('sia_number', 60)->nullable();
            $table->string('apj_name')->nullable();
            $table->string('apj_sipa_number', 60)->nullable();

            // {"senin": {"open": "08:00", "close": "21:00"}, ...} — data, bukan teks bebas,
            // karena nanti dibaca mesin untuk menentukan cabang mana bisa menerima pesanan.
            $table->json('operating_hours')->nullable();

            $table->boolean('supports_delivery')->default(true);
            $table->boolean('supports_pickup')->default(true);
            $table->unsignedSmallInteger('delivery_radius_km')->default(10);

            $table->enum('status', ['aktif', 'tutup sementara', 'tutup permanen'])
                ->default('aktif');

            $table->timestamps();
            $table->softDeletes();

            // Pencarian cabang terdekat selalu menyaring status dulu, baru jarak.
            $table->index(['status', 'latitude', 'longitude']);
            $table->index(['provinsi', 'kota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};

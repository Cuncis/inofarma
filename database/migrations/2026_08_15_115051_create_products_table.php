<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog produk nasional.
 *
 * PENTING: tabel ini TIDAK punya kolom `stock`. Stok selalu milik kombinasi
 * produk × cabang dan tinggal di `branch_stocks`. Menaruh satu angka stok di
 * sini adalah kesalahan yang membuat seluruh model multi-cabang runtuh.
 *
 * `price` adalah harga dasar nasional; cabang boleh menimpanya lewat
 * `branch_stocks.price_override`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 40)->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->restrictOnDelete();

            // Uang selalu bilangan bulat rupiah penuh. Tidak pernah float.
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->unsignedBigInteger('cost_price')->nullable();

            $table->string('unit', 30)->default('Pcs');
            $table->text('blurb')->nullable();
            $table->longText('description')->nullable();

            // --- Wajib untuk produk farmasi ---
            $table->enum('drug_class', ['bebas', 'bebas terbatas', 'keras', 'non-obat'])
                ->default('non-obat');
            $table->string('nie_bpom', 40)->nullable();
            $table->string('composition')->nullable();
            $table->text('indication')->nullable();
            $table->text('dosage')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('warning')->nullable();
            $table->string('manufacturer')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->unsignedSmallInteger('max_qty_per_order')->nullable();
            $table->enum('storage', ['suhu ruang', 'sejuk', 'dingin'])->default('suhu ruang');

            // Dibutuhkan untuk menghitung ongkir.
            $table->unsignedInteger('weight_grams')->default(0);

            $table->unsignedBigInteger('sold_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);

            $table->enum('status', ['aktif', 'nonaktif', 'arsip'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index('drug_class');

            // Pencarian nama obat memakai indeks fulltext di produksi (MySQL).
            // SQLite yang dipakai pengujian tidak mendukungnya, dan pencarian
            // di sana tetap benar lewat LIKE — hanya lebih lambat, yang tidak
            // relevan untuk data uji.
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['name', 'composition']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

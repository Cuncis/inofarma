<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribut produk (Dosis, Bentuk Sediaan, Kemasan, ...): a named, admin-managed
 * vocabulary shown on the product-info screens. Not wired to `products` yet —
 * no attribute is part of any "Selesai bila" criterion, and there is no
 * product-variant concept in this catalogue to attach values to. This is the
 * real CRUD table `Components/Admin/data.js`'s `attributes` fixture used to
 * stand in for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->enum('type', ['pilihan', 'teks'])->default('pilihan');
            // Only meaningful when type = pilihan; null for free-text attributes.
            $table->json('values')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};

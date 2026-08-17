<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kupon diskon. `value` is a percentage point (0-100) for `persentase`, a
 * rupiah amount for `nominal`, and unused (0) for `ongkir gratis`.
 *
 * Redemption tracking (one-per-customer, applying at checkout) belongs to
 * Fase 5's real cart — this table only carries the coupon's own rules and a
 * running `used_count` an order increments when it applies one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->enum('type', ['persentase', 'nominal', 'ongkir gratis']);
            $table->unsignedInteger('value')->default(0);
            $table->unsignedBigInteger('minimum_purchase')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
        });

        // Cabang tempat kupon berlaku. Kosong = berlaku di semua cabang — sama
        // dengan konvensi `users.branch_id IS NULL` untuk staf pusat.
        Schema::create('coupon_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->unique(['coupon_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_branch');
        Schema::dropIfExists('coupons');
    }
};

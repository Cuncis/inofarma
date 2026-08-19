<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dedup guard for `notifikasi:pengambilan-mendekati-batas` (Fase 8) — same
 * reasoning as `inventory_batches.expiry_reminder_sent_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('pickup_reminder_sent_at')->nullable()->after('pickup_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pickup_reminder_sent_at');
        });
    }
};

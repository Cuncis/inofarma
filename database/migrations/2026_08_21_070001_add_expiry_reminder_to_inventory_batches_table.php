<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dedup guard for `notifikasi:produk-kedaluwarsa` (Fase 8) — without it, a
 * batch sitting inside the warning window would renotify branch staff every
 * single day the scheduled sweep runs, for weeks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->timestamp('expiry_reminder_sent_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn('expiry_reminder_sent_at');
        });
    }
};

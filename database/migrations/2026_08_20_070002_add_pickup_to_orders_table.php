<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 7.2: the short code (+ QR of the same code) a pickup customer shows at
 * the counter. Issued when an admin marks the order `siap diambil` — not at
 * checkout, since the code has no meaning until the item is actually staged
 * — with a 48-hour window (ROADMAP.md 0.3's own suggested default; Fase 0
 * never settled on one). `pesanan:kadaluwarsakan-pengambilan` sweeps past it
 * the same way `pesanan:kadaluwarsakan` sweeps unpaid orders past
 * `expires_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_code', 10)->nullable()->after('expires_at');
            $table->timestamp('pickup_code_expires_at')->nullable()->after('pickup_code');
            $table->timestamp('picked_up_at')->nullable()->after('pickup_code_expires_at');
            $table->foreignId('handed_over_by')->nullable()->after('picked_up_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['status', 'pickup_code_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['handed_over_by']);
            $table->dropIndex(['status', 'pickup_code_expires_at']);
            $table->dropColumn(['pickup_code', 'pickup_code_expires_at', 'picked_up_at', 'handed_over_by']);
        });
    }
};

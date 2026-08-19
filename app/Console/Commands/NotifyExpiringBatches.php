<?php

namespace App\Console\Commands;

use App\Models\InventoryBatch;
use App\Models\User;
use App\Notifications\Admin\ExpiringProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * "Produk mendekati kedaluwarsa" (ROADMAP.md Fase 8) — a daily sweep rather
 * than an event, since nothing writes to a batch on the day it happens to
 * cross the warning window. `expiry_reminder_sent_at` stops the same batch
 * from renotifying every day for the rest of that window.
 */
class NotifyExpiringBatches extends Command
{
    protected $signature = 'notifikasi:produk-kedaluwarsa';

    protected $description = 'Beri tahu staf cabang untuk batch yang mendekati tanggal kedaluwarsa';

    private const WARNING_DAYS = 30;

    public function handle(): int
    {
        $batches = InventoryBatch::withoutGlobalScopes()
            ->with(['product', 'branch'])
            ->where('quantity', '>', 0)
            ->whereNull('expiry_reminder_sent_at')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(self::WARNING_DAYS)])
            ->get();

        foreach ($batches as $batch) {
            $staff = User::where('branch_id', $batch->branch_id)->where('is_active', true)->get();

            if ($staff->isNotEmpty()) {
                Notification::send($staff, new ExpiringProduct($batch));
            }

            $batch->update(['expiry_reminder_sent_at' => now()]);
            $this->line("Diberitahukan: batch {$batch->batch_number} ({$batch->product->name})");
        }

        $this->info("{$batches->count()} batch mendekati kedaluwarsa diberitahukan.");

        return self::SUCCESS;
    }
}

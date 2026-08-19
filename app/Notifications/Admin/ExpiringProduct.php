<?php

namespace App\Notifications\Admin;

use App\Models\InventoryBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Produk mendekati kedaluwarsa" (ROADMAP.md Fase 8) — fired once per batch
 * by `notifikasi:produk-kedaluwarsa` (daily), guarded by
 * `inventory_batches.expiry_reminder_sent_at` so the same batch doesn't
 * renotify every day for the rest of its warning window.
 */
class ExpiringProduct extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly InventoryBatch $batch) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $batch = $this->batch;

        return [
            'title' => 'Batch mendekati kedaluwarsa',
            'body' => "{$batch->product->name} (batch {$batch->batch_number}): kedaluwarsa {$batch->expires_at->translatedFormat('d M Y')}, sisa {$batch->quantity}",
            'link' => route('admin.inventaris.stok.show', $batch->branch->code),
        ];
    }
}

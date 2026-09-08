<?php

namespace App\Notifications\Admin;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use App\Models\InventoryBatch;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Produk mendekati kedaluwarsa" (ROADMAP.md Fase 8) — fired once per batch
 * by `notifikasi:produk-kedaluwarsa` (daily), guarded by
 * `inventory_batches.expiry_reminder_sent_at` so the same batch doesn't
 * renotify every day for the rest of its warning window.
 *
 * Still a plain queued Laravel notification (delivery stays `$user->notify()`
 * via the standard `database` channel) — only the stored payload shape comes
 * from `Filament\Notifications\Notification::getDatabaseMessage()`, which is
 * what stamps `data->format = 'filament'` so the admin panel's own bell
 * (`->databaseNotifications()`) picks it up.
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

        return FilamentNotification::make()
            ->title('Batch mendekati kedaluwarsa')
            ->body("{$batch->product->name} (batch {$batch->batch_number}): kedaluwarsa {$batch->expires_at->translatedFormat('d M Y')}, sisa {$batch->quantity}")
            ->actions([
                Action::make('view')->label('Lihat Stok')->url(BranchStockResource::getUrl()),
            ])
            ->getDatabaseMessage();
    }
}

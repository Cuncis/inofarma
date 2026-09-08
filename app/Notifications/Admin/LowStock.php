<?php

namespace App\Notifications\Admin;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use App\Models\BranchStock;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Stok menipis" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\BranchStockObserver` the moment a branch's available stock
 * crosses at/below its `reorder_point`, not on every write after that (see
 * the observer for the before/after comparison that prevents a repeat ping
 * on every subsequent sale of an already-low product).
 *
 * Still a plain queued Laravel notification (delivery stays `$user->notify()`
 * via the standard `database` channel) — only the stored payload shape comes
 * from `Filament\Notifications\Notification::getDatabaseMessage()`, which is
 * what stamps `data->format = 'filament'` so the admin panel's own bell
 * (`->databaseNotifications()`) picks it up.
 */
class LowStock extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly BranchStock $stock) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $stock = $this->stock;

        return FilamentNotification::make()
            ->title('Stok menipis')
            ->body("{$stock->product->name}: tersisa {$stock->available} (batas {$stock->reorder_point})")
            ->actions([
                Action::make('view')->label('Lihat Stok')->url(BranchStockResource::getUrl()),
            ])
            ->getDatabaseMessage();
    }
}

<?php

namespace App\Notifications\Admin;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Pesanan baru di cabangnya" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\OrderObserver::created()` to every active staff member of
 * the order's branch. Database-channel only: this is the admin panel's own
 * bell (`->databaseNotifications()`), not an email.
 *
 * Still a plain queued Laravel notification (delivery stays `$user->notify()`
 * via the standard `database` channel) — only the stored payload shape comes
 * from `Filament\Notifications\Notification::getDatabaseMessage()`, which is
 * what stamps `data->format = 'filament'` so the panel's bell picks it up.
 */
class NewOrderAtBranch extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $order = $this->order;

        return FilamentNotification::make()
            ->title('Pesanan baru')
            ->body("#{$order->number} — ".Money::rupiah($order->grand_total))
            ->actions([
                Action::make('view')->label('Lihat Pesanan')->url(OrderResource::getUrl('view', ['record' => $order])),
            ])
            ->getDatabaseMessage();
    }
}

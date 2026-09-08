<?php

namespace App\Notifications\Admin;

use App\Filament\Resources\Pickups\PickupResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Pesanan belum diambil mendekati batas" (ROADMAP.md Fase 8) — fired once
 * per order by `notifikasi:pengambilan-mendekati-batas` (hourly), guarded
 * by `orders.pickup_reminder_sent_at`. An internal nudge for branch staff to
 * follow up with the customer — distinct from `OrderReadyForPickup`, which
 * already told the *customer* once when the order first became ready.
 *
 * Still a plain queued Laravel notification (delivery stays `$user->notify()`
 * via the standard `database` channel) — only the stored payload shape comes
 * from `Filament\Notifications\Notification::getDatabaseMessage()`, which is
 * what stamps `data->format = 'filament'` so the admin panel's own bell
 * (`->databaseNotifications()`) picks it up.
 */
class PickupDeadlineApproaching extends Notification implements ShouldQueue
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
            ->title('Batas ambil mendekat')
            ->body("#{$order->number} belum diambil, berlaku sampai {$order->pickup_code_expires_at?->translatedFormat('d M Y, H:i')}")
            ->actions([
                Action::make('view')->label('Lihat Pengambilan')->url(PickupResource::getUrl()),
            ])
            ->getDatabaseMessage();
    }
}

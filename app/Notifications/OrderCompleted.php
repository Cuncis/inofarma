<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Selesai" (ROADMAP.md Fase 8) — fired by `App\Observers\OrderObserver::updated()`
 * when `status` becomes `selesai`: a delivery marked `delivered` by
 * Biteship's webhook, or a pickup handed over at the counter
 * (`PickupCodeService::handOver()`), Fase 7 either way.
 */
class OrderCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $verb = $order->fulfilment === 'ambil' ? 'diambil' : 'diterima';

        return (new MailMessage)
            ->subject("Pesanan #{$order->number} Selesai — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan Anda #{$order->number} sudah {$verb}. Terima kasih sudah berbelanja di Inofarma!")
            ->action('Beri Ulasan', route('ui.track-order', $order->number));
    }
}

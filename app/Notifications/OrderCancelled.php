<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Dibatalkan" (ROADMAP.md Fase 8) — fired by `App\Observers\OrderObserver::updated()`
 * for both `dibatalkan` (customer's own cancellation, Fase 5) and
 * `kedaluwarsa` (a payment or pickup window lapsing, Fase 6/7's scheduled
 * sweeps) — same message either way, since `App\Support\OrderCancellation`
 * already treats both as "the stock came back, nothing to fulfil."
 */
class OrderCancelled extends Notification implements ShouldQueue
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
        $reason = $order->status === 'kedaluwarsa'
            ? 'karena melewati batas waktu'
            : 'sesuai permintaan Anda';

        return (new MailMessage)
            ->subject("Pesanan #{$order->number} Dibatalkan — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan Anda #{$order->number} dibatalkan {$reason}.")
            ->line('Bila sudah membayar, dana akan diproses sesuai kebijakan pengembalian dana.')
            ->action('Belanja Lagi', route('home'));
    }
}

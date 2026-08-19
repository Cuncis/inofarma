<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Bukti bayar" (ROADMAP.md Fase 8) — fired by `App\Observers\OrderObserver::updated()`
 * the moment `payment_status` becomes `lunas`, whichever path got it there:
 * DOKU's webhook (Fase 6), or a Tunai order settled by staff.
 */
class PaymentReceived extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("Bukti Bayar Pesanan #{$order->number} — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pembayaran untuk pesanan #{$order->number} sudah kami terima.")
            ->line('Jumlah: '.Money::rupiah($order->grand_total))
            ->line('Metode: '.($order->payment_method ?? '—'))
            ->action('Lihat Pesanan', route('ui.track-order', $order->number))
            ->line('Pesanan Anda sekarang sedang diproses.');
    }
}

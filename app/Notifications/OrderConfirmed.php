<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Konfirmasi pesanan" (ROADMAP.md Fase 8) — fired by `App\Observers\OrderObserver::created()`
 * for every order, `antar` or `ambil`, `online` or `Tunai` alike.
 */
class OrderConfirmed extends Notification implements ShouldQueue
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
            ->subject("Pesanan #{$order->number} Diterima — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan Anda #{$order->number} dari {$order->branch->name} sudah kami terima.")
            ->line('Total: '.Money::rupiah($order->grand_total))
            ->line($order->fulfilment === 'antar' ? 'Pesanan akan diantar ke alamat Anda.' : 'Pesanan bisa diambil di cabang setelah siap.')
            ->action('Lacak Pesanan', route('ui.track-order', $order->number))
            ->line('Terima kasih sudah berbelanja di Inofarma.');
    }
}

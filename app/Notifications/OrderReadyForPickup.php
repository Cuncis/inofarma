<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Siap diambil + kode" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\OrderObserver::updated()` when `status` becomes
 * `siap diambil` (`PickupCodeService::issue()`, Fase 7). The other of the
 * two WhatsApp-carried notifications Fase 8 asks for.
 */
class OrderReadyForPickup extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return (new MailMessage)
            ->subject("Pesanan #{$order->number} Siap Diambil — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan Anda #{$order->number} sudah siap diambil di {$order->branch->name}.")
            ->line("Kode ambil Anda: {$order->pickup_code}")
            ->line("Tunjukkan kode ini (atau QR di halaman lacak pesanan) ke kasir. Berlaku sampai {$order->pickup_code_expires_at?->translatedFormat('d M Y, H:i')}.")
            ->action('Lihat Kode Ambil', route('ui.track-order', $order->number));
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $order = $this->order;

        return new WhatsAppMessage(
            template: config('services.whatsapp.templates.order_ready_for_pickup'),
            parameters: [$notifiable->name, $order->number, (string) $order->pickup_code],
            logFallback: "Pesanan #{$order->number} siap diambil. Kode: {$order->pickup_code}.",
        );
    }
}

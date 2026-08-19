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
 * "Pesanan dikirim + resi" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\OrderObserver::updated()` when `status` becomes `dikirim`
 * (`Admin\OrderController::ship()`, Fase 7). One of the two notifications
 * Fase 8 explicitly asks to also go out over WhatsApp ("kurir berangkat").
 */
class OrderShipped extends Notification implements ShouldQueue
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
        $shipment = $order->shipment;

        $message = (new MailMessage)
            ->subject("Pesanan #{$order->number} Sedang Dikirim — Inofarma")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pesanan Anda #{$order->number} sudah diserahkan ke kurir.");

        if ($shipment) {
            $message->line("Kurir: {$shipment->courier_name} {$shipment->courier_service_name}");

            if ($shipment->waybill_id) {
                $message->line("No. Resi: {$shipment->waybill_id}");
            }
        }

        return $message
            ->action('Lacak Pesanan', route('ui.track-order', $order->number))
            ->line('Kami akan mengabari Anda lagi begitu pesanan sampai.');
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $order = $this->order;
        $waybill = $order->shipment?->waybill_id ?? '-';

        return new WhatsAppMessage(
            template: config('services.whatsapp.templates.order_shipped'),
            parameters: [$notifiable->name, $order->number, $waybill],
            logFallback: "Pesanan #{$order->number} sedang dikirim. No. Resi: {$waybill}.",
        );
    }
}

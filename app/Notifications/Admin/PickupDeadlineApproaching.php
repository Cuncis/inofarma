<?php

namespace App\Notifications\Admin;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Pesanan belum diambil mendekati batas" (ROADMAP.md Fase 8) — fired once
 * per order by `notifikasi:pengambilan-mendekati-batas` (hourly), guarded
 * by `orders.pickup_reminder_sent_at`. An internal nudge for branch staff to
 * follow up with the customer — distinct from `OrderReadyForPickup`, which
 * already told the *customer* once when the order first became ready.
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

        return [
            'title' => 'Batas ambil mendekat',
            'body' => "#{$order->number} belum diambil, berlaku sampai {$order->pickup_code_expires_at?->translatedFormat('d M Y, H:i')}",
            'link' => route('admin.pengambilan.index'),
        ];
    }
}

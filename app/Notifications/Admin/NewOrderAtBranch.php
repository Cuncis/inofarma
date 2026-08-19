<?php

namespace App\Notifications\Admin;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Pesanan baru di cabangnya" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\OrderObserver::created()` to every active staff member of
 * the order's branch. Database-channel only: this is the admin topbar bell,
 * not an email — see `App\Support\Presenters\AdminNotificationPresenter`.
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

        return [
            'title' => 'Pesanan baru',
            'body' => "#{$order->number} — ".Money::rupiah($order->grand_total),
            'link' => route('admin.pesanan.show', $order->number),
        ];
    }
}

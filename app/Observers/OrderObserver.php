<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\Admin\NewOrderAtBranch;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCompleted;
use App\Notifications\OrderConfirmed;
use App\Notifications\OrderReadyForPickup;
use App\Notifications\OrderShipped;
use App\Notifications\PaymentReceived;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Notification;

/**
 * The single place every customer- and branch-facing order notification
 * fires from (ROADMAP.md Fase 8's "setiap perubahan status memicu pesan
 * yang benar"). Deliberately an observer rather than a `->notify()` call
 * scattered through `CheckoutController`, `DokuPaymentService`,
 * `ShipmentService`, `PickupCodeService`, `OrderCancellation` and
 * `Admin\OrderController` — every one of those already exists from Fase
 * 5-7 and already ends in an `Order::create()`/`update()`; this reacts to
 * that write instead of asking six call sites to each remember to notify.
 *
 * Also where "jejak audit ... setiap penjualan, per cabang" (Fase 9.3) is
 * satisfied — `AuditLogger::log()` here runs for a checkout-placed order
 * (no signed-in staff, `AuditLogger` defaults the actor to null) exactly the
 * same as an admin-entered one (actor resolves to whoever is signed in),
 * with `branch_id` passed explicitly so it's always the order's own branch
 * rather than the acting staff member's (which is null for a customer).
 */
class OrderObserver
{
    public function created(Order $order): void
    {
        $order->customer?->notify(new OrderConfirmed($order));

        $this->notifyBranchStaff($order, new NewOrderAtBranch($order));

        AuditLogger::log('pesanan_dibuat', $order, [], [
            'number' => $order->number, 'grand_total' => $order->grand_total, 'fulfilment' => $order->fulfilment,
        ], branchId: $order->branch_id);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status') && $order->payment_status === 'lunas') {
            $order->customer?->notify(new PaymentReceived($order));
        }

        if ($order->wasChanged('status')) {
            $notification = match ($order->status) {
                'dikirim' => new OrderShipped($order),
                'siap diambil' => new OrderReadyForPickup($order),
                'selesai' => new OrderCompleted($order),
                'dibatalkan', 'kedaluwarsa' => new OrderCancelled($order),
                default => null,
            };

            if ($notification) {
                $order->customer?->notify($notification);
            }
        }
    }

    private function notifyBranchStaff(Order $order, object $notification): void
    {
        $staff = User::where('branch_id', $order->branch_id)->where('is_active', true)->get();

        if ($staff->isEmpty()) {
            return;
        }

        // `$staff` isn't a single Notifiable model, so `Notification::send()`
        // (not `$model->notify()`) is what fans this out to every recipient.
        Notification::send($staff, $notification);
    }
}

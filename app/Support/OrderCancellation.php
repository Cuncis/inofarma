<?php

namespace App\Support;

use App\Models\Order;
use App\Support\Inventory\StockAllocator;
use Illuminate\Support\Facades\DB;

/**
 * Undoes an order's stock consumption and marks it `dibatalkan` or
 * `kedaluwarsa` — the one place that returns stock to the exact batches
 * `CheckoutController` took it from (`order_items.batches_consumed`).
 *
 * Three callers, same rule ("give the stock back, stop the coupon counting
 * it as used"), different reasons: a customer cancelling before processing
 * (`Shop\OrderController::cancel()`), the 24-hour payment window lapsing
 * (`pesanan:kadaluwarsakan`), and DOKU itself reporting a checkout session
 * expired before the customer paid (`DokuPaymentService::applyToOrder()`).
 */
class OrderCancellation
{
    public static function apply(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            $order->loadMissing('items.product', 'branch', 'coupon');
            $allocator = new StockAllocator;

            foreach ($order->items as $item) {
                if (! $item->batches_consumed || ! $item->product_id) {
                    continue;
                }

                $allocator->receive(
                    $order->branch,
                    $item->product,
                    $item->batches_consumed,
                    'retur masuk',
                    $order,
                );
            }

            $order->coupon?->decrement('used_count');

            $order->update([
                'status' => $status,
                'cancelled_at' => now(),
            ]);
        });
    }
}

<?php

namespace App\Support\Presenters;

use App\Models\Order;
use App\Support\AdminOptions;

/**
 * Turns an `Order` into the shape the admin screens expect.
 *
 * Money comes from the order's own columns, never recomputed from today's
 * catalogue: an order is a record of what somebody was charged, so repricing a
 * product afterwards must leave it untouched. Line items likewise read their
 * snapshotted name and price, not the product relation.
 */
class OrderPresenter
{
    /**
     * @param  iterable<Order>  $orders
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $orders): array
    {
        return collect($orders)->map(fn (Order $order) => self::toArray($order))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Order $order): array
    {
        $items = $order->items->map(fn ($item) => [
            'productId' => $item->sku,
            'name' => $item->product_name,
            'price' => $item->unit_price,
            'qty' => $item->quantity,
        ])->values()->all();

        return [
            'id' => $order->number,
            'customerId' => $order->customer?->code,
            'customerName' => $order->customer?->name ?? '—',
            'customerEmail' => $order->customer?->email,
            'customerAvatar' => $order->customer?->avatar_path,
            'branch' => $order->branch?->code,
            'branchName' => $order->branch?->name,
            'fulfilment' => AdminOptions::toLabel(AdminOptions::FULFILMENTS, $order->fulfilment),
            'date' => $order->created_at?->translatedFormat('d M Y'),
            'payment' => $order->payment_method,
            'status' => AdminOptions::toLabel(AdminOptions::ORDER_STATUSES, $order->status),
            'shipping' => $order->shipping_total,
            'note' => $order->note ?? '',
            'items' => $items,
            'subtotal' => $order->subtotal,
            'total' => $order->grand_total,
            'itemCount' => array_sum(array_column($items, 'qty')),
        ];
    }
}

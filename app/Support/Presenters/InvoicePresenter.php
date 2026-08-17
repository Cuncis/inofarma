<?php

namespace App\Support\Presenters;

use App\Models\Order;

/**
 * "Faktur" is not a table of its own — it is an `Order` read as an invoice.
 * Payment status doesn't exist as a separate concept yet (that's Fase 6), so
 * building a standalone `invoices` model now would just invent a second
 * "paid/unpaid" flag Fase 6 would then have to reconcile with the real one.
 * `orders.payment_status` and `orders.expires_at` already say everything a
 * faktur needs to say.
 */
class InvoicePresenter
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
        return [
            'number' => $order->number,
            'customer' => $order->customer?->name,
            'issued' => $order->created_at?->translatedFormat('d M Y'),
            'due' => $order->expires_at?->translatedFormat('d M Y') ?? '—',
            'total' => $order->grand_total,
            'status' => self::status($order),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function withLines(Order $order): array
    {
        return [
            ...self::toArray($order),
            'branch' => $order->branch?->name,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_total,
            'shipping' => $order->shipping_total,
            'tax' => $order->tax_total,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'qty' => $item->quantity,
                'price' => $item->unit_price,
            ])->all(),
        ];
    }

    private static function status(Order $order): string
    {
        return match (true) {
            $order->payment_status === 'lunas' => 'Lunas',
            $order->payment_status === 'refund' => 'Refund',
            $order->expires_at?->isPast() => 'Jatuh Tempo',
            default => 'Belum Bayar',
        };
    }
}

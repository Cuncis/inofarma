<?php

namespace App\Support\Presenters;

use App\Models\Order;

/**
 * "Faktur" is not a table of its own — it is an `Order` read as an invoice.
 * `orders.payment_status` and `orders.expires_at` remain the one thing every
 * other screen reads for "is this paid" — `payments` (Fase 6) is a gateway
 * attempt log underneath it, shown here for context, never a second source
 * of truth this presenter has to reconcile against.
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
            'isRefundable' => $order->payment_status === 'lunas',
            'payments' => $order->payments->map(fn ($payment) => [
                'invoiceNumber' => $payment->invoice_number,
                'status' => ucfirst($payment->status),
                'channel' => $payment->channel,
                'amount' => $payment->amount,
                'createdAt' => $payment->created_at?->translatedFormat('d M Y, H:i'),
                'paidAt' => $payment->paid_at?->translatedFormat('d M Y, H:i'),
            ])->values()->all(),
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

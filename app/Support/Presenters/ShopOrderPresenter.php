<?php

namespace App\Support\Presenters;

use App\Models\Order;
use App\Support\AdminOptions;
use App\Support\Pickup\PickupCodeService;

/**
 * A customer's own view of their order — history list and the tracking
 * timeline. Unlike `OrderPresenter` (the admin's view), this never exposes
 * another customer's data because every `Order` query here is already scoped
 * to `auth('customer')->user()->orders()` by the controller.
 */
class ShopOrderPresenter
{
    /**
     * @param  iterable<Order>  $orders
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $orders): array
    {
        return collect($orders)->map(fn (Order $order) => self::summary($order))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function summary(Order $order): array
    {
        return [
            'number' => $order->number,
            'status' => AdminOptions::toLabel(AdminOptions::ORDER_STATUSES, $order->status),
            'fulfilment' => AdminOptions::toLabel(AdminOptions::FULFILMENTS, $order->fulfilment),
            'date' => $order->created_at?->translatedFormat('d M Y'),
            'total' => $order->grand_total,
            'itemCount' => $order->item_count,
            'branchName' => $order->branch?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Order $order): array
    {
        return [
            ...self::summary($order),
            'branch' => $order->branch ? CartPresenter::branch($order->branch) : null,
            'recipientName' => $order->recipient_name,
            'recipientPhone' => $order->recipient_phone,
            'shippingAddress' => $order->shipping_address,
            'paymentMethod' => $order->payment_method,
            'paymentStatus' => $order->payment_status,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_total,
            'shipping' => $order->shipping_total,
            'tax' => $order->tax_total,
            'note' => $order->note,
            'isCancellable' => $order->is_cancellable_by_customer,
            // "online" is the only payment_method that ever routes through
            // DOKU — a "Tunai" order is settled at the counter, never here.
            'canPay' => $order->status === 'menunggu pembayaran'
                && $order->payment_status === 'belum bayar'
                && $order->payment_method === 'online',
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'sku' => $item->sku,
                'unitPrice' => $item->unit_price,
                'quantity' => $item->quantity,
                'lineTotal' => $item->line_total,
            ])->values()->all(),
            'steps' => self::steps($order),
            'shipment' => self::shipment($order),
            'pickup' => self::pickup($order),
        ];
    }

    /** @return ?array<string, mixed> */
    private static function shipment(Order $order): ?array
    {
        $shipment = $order->relationLoaded('shipment') ? $order->shipment : $order->shipment()->first();

        if (! $shipment) {
            return null;
        }

        return [
            'courierName' => $shipment->courier_name,
            'serviceName' => $shipment->courier_service_name,
            'waybillId' => $shipment->waybill_id,
            'trackingLink' => $shipment->courier_link,
            'statusLabel' => self::shipmentStatusLabel($shipment->status),
        ];
    }

    /** Biteship's own status vocabulary, in Indonesian, for a customer who has never heard the English word "droppingOff". */
    private static function shipmentStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'confirmed' => 'Pesanan dikonfirmasi, mencari kurir',
            'allocated' => 'Kurir ditugaskan',
            'pickingUp' => 'Kurir menuju cabang',
            'picked' => 'Barang diambil kurir',
            'inTransit' => 'Dalam perjalanan',
            'droppingOff' => 'Menuju alamat Anda',
            'delivered' => 'Terkirim',
            'onHold' => 'Tertahan sementara',
            'returnInTransit', 'returned' => 'Dikembalikan ke cabang',
            'rejected' => 'Ditolak kurir',
            'courierNotFound' => 'Kurir tidak tersedia',
            'cancelled' => 'Dibatalkan',
            'disposed' => 'Dimusnahkan',
            default => null,
        };
    }

    /** @return ?array<string, mixed> */
    private static function pickup(Order $order): ?array
    {
        if (! $order->pickup_code || $order->status !== 'siap diambil') {
            return null;
        }

        return [
            'code' => $order->pickup_code,
            'qrSvg' => PickupCodeService::qrSvgDataUri($order),
            'expiresAt' => $order->pickup_code_expires_at?->translatedFormat('d M Y, H:i'),
        ];
    }

    /**
     * The tracking timeline. Empty for a cancelled/expired order — those get
     * their own banner in the UI instead of a progress bar that implies
     * forward motion.
     *
     * @return list<array{label: string, state: 'done'|'current'|'pending', at: ?string}>
     */
    public static function steps(Order $order): array
    {
        if (in_array($order->status, ['dibatalkan', 'kedaluwarsa'], true)) {
            return [];
        }

        $pickup = $order->fulfilment === 'ambil';

        $definitions = [
            ['status' => 'menunggu pembayaran', 'label' => 'Pesanan Dibuat', 'at' => $order->created_at],
            ['status' => 'diproses', 'label' => 'Diproses', 'at' => null],
            [
                'status' => $pickup ? 'siap diambil' : 'dikirim',
                'label' => $pickup ? 'Siap Diambil' : 'Dikirim',
                'at' => $order->ready_at,
            ],
            ['status' => 'selesai', 'label' => $pickup ? 'Diambil' : 'Diterima', 'at' => $order->completed_at],
        ];

        $statuses = array_column($definitions, 'status');
        $currentIndex = array_search($order->status, $statuses, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $lastIndex = count($definitions) - 1;

        return collect($definitions)->values()->map(fn (array $step, int $index) => [
            'label' => $step['label'],
            'state' => match (true) {
                $index < $currentIndex => 'done',
                // The final step reaching its own status means the order is
                // fully complete, not merely "in progress" — a checkmark, not
                // a highlighted ring.
                $index === $currentIndex && $index === $lastIndex => 'done',
                $index === $currentIndex => 'current',
                default => 'pending',
            },
            'at' => $step['at']?->translatedFormat('d M Y, H:i'),
        ])->all();
    }
}

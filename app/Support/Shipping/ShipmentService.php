<?php

namespace App\Support\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Support\Shipping\Biteship\BiteshipClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orchestrates one order's relationship with Biteship past the checkout-time
 * quote `ShippingQuoteService` already priced: actually booking the pickup
 * (`bookForOrder()`, an admin-cabang action per ROADMAP.md 7.1 — "buat label
 * dan resi dari admin cabang", never done automatically at checkout), and
 * folding a webhook event back into `Shipment`/`Order` state
 * (`applyWebhookEvent()`).
 */
class ShipmentService
{
    public function __construct(private readonly BiteshipClient $client) {}

    public static function make(): self
    {
        return new self(BiteshipClient::fromConfig());
    }

    /**
     * @throws RuntimeException if the order has no `antar` shipment quote, or
     *                          one has already been booked
     */
    public function bookForOrder(Order $order): Shipment
    {
        $order->loadMissing('shipment', 'items.product', 'branch');
        $shipment = $order->shipment;

        if (! $shipment) {
            throw new RuntimeException("Pesanan #{$order->number} tidak punya kurir yang dipilih.");
        }

        if ($shipment->is_booked) {
            throw new RuntimeException("Resi untuk pesanan #{$order->number} sudah dibuat.");
        }

        $branch = $order->branch;

        $response = $this->client->createOrder([
            'origin_contact_name' => $branch->name,
            'origin_contact_phone' => $branch->phone ?: $branch->whatsapp,
            'origin_address' => $branch->address_line,
            'origin_postal_code' => $branch->postal_code,
            'origin_coordinate' => [
                'latitude' => (float) $branch->latitude,
                'longitude' => (float) $branch->longitude,
            ],
            'destination_contact_name' => $order->recipient_name,
            'destination_contact_phone' => $order->recipient_phone,
            'destination_address' => $order->shipping_address,
            'destination_coordinate' => [
                'latitude' => (float) $order->shipping_latitude,
                'longitude' => (float) $order->shipping_longitude,
            ],
            'courier_company' => $shipment->courier_company,
            'courier_type' => $shipment->courier_type,
            'delivery_type' => 'now',
            'order_note' => $order->note,
            'reference_id' => $order->number,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'value' => $item->unit_price,
                'quantity' => $item->quantity,
                'weight' => $item->product?->weight_grams ?? 0,
                'height' => $item->product?->height_cm ?? 0,
                'length' => $item->product?->length_cm ?? 0,
                'width' => $item->product?->width_cm ?? 0,
            ])->all(),
        ]);

        $courier = $response['courier'] ?? [];

        $shipment->update([
            'biteship_order_id' => $response['id'] ?? null,
            'tracking_id' => $courier['tracking_id'] ?? null,
            'waybill_id' => $courier['waybill_id'] ?? null,
            'courier_link' => $courier['link'] ?? null,
            'status' => $response['status'] ?? 'confirmed',
            'history' => [['status' => $response['status'] ?? 'confirmed', 'at' => now()->toIso8601String()]],
            'raw_response' => $response,
            'shipped_at' => now(),
        ]);

        $order->update(['status' => 'dikirim', 'ready_at' => now()]);

        return $shipment->fresh();
    }

    /**
     * Fold one Biteship webhook event (`order.status`, `order.waybill_id`,
     * or `order.price`) into `Shipment`/`Order` state. Idempotent: resending
     * the same status again updates the raw payload but never re-cascades
     * into `Order` a second time.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function applyWebhookEvent(array $payload): ?Shipment
    {
        $biteshipOrderId = $payload['order_id'] ?? null;

        if (! $biteshipOrderId) {
            return null;
        }

        $shipment = Shipment::where('biteship_order_id', $biteshipOrderId)->first();

        if (! $shipment) {
            return null;
        }

        return self::applyStatus(
            $shipment,
            $payload['status'] ?? $shipment->status,
            $payload['courier_waybill_id'] ?? null,
            $payload['courier_tracking_id'] ?? null,
            $payload,
        );
    }

    /**
     * Manually pull Biteship's own tracking record for one shipment and fold
     * it in the same way a webhook event would — for a shipment whose
     * webhook is late, was lost, or (in local development, where Biteship
     * can't reach `localhost`) was never going to arrive at all. Unlike
     * `applyWebhookEvent()` this is handed the `Shipment` directly rather
     * than needing to look one up by `order_id` out of a payload, so it goes
     * straight to `applyStatus()`.
     *
     * @throws RuntimeException on a Biteship-side failure, or if this
     *                          shipment was never actually booked yet
     */
    public function reconcile(Shipment $shipment): Shipment
    {
        if (! $shipment->tracking_id) {
            throw new RuntimeException("Pesanan #{$shipment->order?->number} belum punya resi untuk dilacak.");
        }

        $response = $this->client->track($shipment->tracking_id);

        return self::applyStatus(
            $shipment,
            $response['status'] ?? $shipment->status,
            $response['waybill_id'] ?? $response['courier']['waybill_id'] ?? null,
            $response['tracking_id'] ?? $response['courier']['tracking_id'] ?? null,
            $response,
        );
    }

    /** Shared by both the webhook and the manual `reconcile()` — same state transition either way. */
    private static function applyStatus(
        Shipment $shipment,
        string $status,
        ?string $waybillId,
        ?string $trackingId,
        array $raw,
    ): Shipment {
        return DB::transaction(function () use ($shipment, $status, $waybillId, $trackingId, $raw) {
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->first();
            $changed = $status !== $shipment->status;

            $history = $shipment->history ?? [];
            if ($changed) {
                $history[] = ['status' => $status, 'at' => now()->toIso8601String()];
            }

            $shipment->update([
                'status' => $status,
                'waybill_id' => $waybillId ?? $shipment->waybill_id,
                'tracking_id' => $trackingId ?? $shipment->tracking_id,
                'history' => $history,
                'raw_response' => $raw,
                'delivered_at' => $status === 'delivered' ? ($shipment->delivered_at ?? now()) : $shipment->delivered_at,
            ]);

            if ($changed && $status === 'delivered') {
                $order = $shipment->order()->first();

                if ($order && $order->status === 'dikirim') {
                    $order->update(['status' => 'selesai', 'completed_at' => now()]);
                }
            }

            return $shipment;
        });
    }
}

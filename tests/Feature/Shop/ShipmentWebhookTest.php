<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Biteship's notification is the only source of truth for shipment status
 * (mirrors DOKU's `PaymentWebhookTest`, Fase 6) — except Biteship's own docs
 * describe no signature scheme at all, so what stands in for one here is the
 * `?token=` query string `BiteshipWebhookController` checks — see
 * `services.biteship.webhook_token`.
 */
class ShipmentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-webhook-token';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.biteship.webhook_token' => self::TOKEN]);
    }

    private function makeBookedShipment(): Shipment
    {
        $order = Order::factory()->create(['fulfilment' => 'antar', 'status' => 'dikirim']);

        return Shipment::factory()->for($order)->booked()->create();
    }

    public function test_a_notification_with_a_missing_token_is_rejected(): void
    {
        $shipment = $this->makeBookedShipment();

        $this->postJson('/biteship/notifikasi', [
            'order_id' => $shipment->biteship_order_id, 'status' => 'delivered',
        ])->assertStatus(401);

        $this->assertSame('confirmed', $shipment->fresh()->status);
    }

    public function test_a_notification_with_the_wrong_token_is_rejected(): void
    {
        $shipment = $this->makeBookedShipment();

        $this->postJson('/biteship/notifikasi?token=bogus', [
            'order_id' => $shipment->biteship_order_id, 'status' => 'delivered',
        ])->assertStatus(401);

        $this->assertSame('confirmed', $shipment->fresh()->status);
    }

    public function test_a_verified_delivered_notification_completes_the_order(): void
    {
        $shipment = $this->makeBookedShipment();

        $this->postJson('/biteship/notifikasi?token='.self::TOKEN, [
            'order_id' => $shipment->biteship_order_id,
            'status' => 'delivered',
            'courier_tracking_id' => $shipment->tracking_id,
            'courier_waybill_id' => $shipment->waybill_id,
        ])->assertOk();

        $shipment->refresh();
        $order = $shipment->order->fresh();

        $this->assertSame('delivered', $shipment->status);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertSame('selesai', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_an_intransit_notification_updates_status_without_completing_the_order(): void
    {
        $shipment = $this->makeBookedShipment();

        $this->postJson('/biteship/notifikasi?token='.self::TOKEN, [
            'order_id' => $shipment->biteship_order_id, 'status' => 'inTransit',
        ])->assertOk();

        $shipment->refresh();
        $this->assertSame('inTransit', $shipment->status);
        $this->assertSame('dikirim', $shipment->order->fresh()->status);
    }

    public function test_a_duplicate_delivered_notification_does_not_double_apply(): void
    {
        $shipment = $this->makeBookedShipment();

        $this->postJson('/biteship/notifikasi?token='.self::TOKEN, [
            'order_id' => $shipment->biteship_order_id, 'status' => 'delivered',
        ])->assertOk();

        $deliveredAt = $shipment->fresh()->delivered_at;

        $this->postJson('/biteship/notifikasi?token='.self::TOKEN, [
            'order_id' => $shipment->biteship_order_id, 'status' => 'delivered',
        ])->assertOk();

        $this->assertTrue($deliveredAt->equalTo($shipment->fresh()->delivered_at));
        $this->assertCount(1, $shipment->fresh()->history);
    }

    public function test_a_notification_for_an_unknown_biteship_order_id_is_acknowledged_without_error(): void
    {
        $this->postJson('/biteship/notifikasi?token='.self::TOKEN, [
            'order_id' => 'does-not-exist', 'status' => 'delivered',
        ])->assertOk();
    }
}

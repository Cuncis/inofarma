<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * "Buat label dan resi dari admin cabang" (ROADMAP.md 7.1) — booking the
 * actual Biteship waybill for an order whose courier was already quoted and
 * chosen at checkout (`Shop\CheckoutController`, Fase 7's own tests).
 */
class ShipmentTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
        config(['services.biteship.api_key' => 'test-key']);
    }

    private function makeOrderWithQuotedShipment(): Order
    {
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['weight_grams' => 500]);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'fulfilment' => 'antar', 'status' => 'diproses',
            'recipient_name' => 'Budi', 'recipient_phone' => '0812',
            'shipping_address' => 'Jl. Contoh No. 1', 'shipping_latitude' => -6.2, 'shipping_longitude' => 106.8,
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 10000, 'quantity' => 1, 'line_total' => 10000, 'batches_consumed' => $manifest,
        ]);

        Shipment::factory()->for($order)->create();

        return $order->fresh();
    }

    public function test_an_admin_can_book_a_waybill_for_a_quoted_shipment(): void
    {
        Http::fake([
            'api.biteship.com/v1/orders' => Http::response([
                'id' => 'biteship-order-1',
                'status' => 'confirmed',
                'courier' => ['tracking_id' => 'BST-1', 'waybill_id' => 'JNE001', 'link' => 'https://biteship.com/t/1'],
            ], 200),
        ]);

        $order = $this->makeOrderWithQuotedShipment();

        $this->post("/admin/pesanan/{$order->number}/kirim")
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('dikirim', $order->status);
        $this->assertNotNull($order->ready_at);

        $shipment = $order->shipment;
        $this->assertSame('biteship-order-1', $shipment->biteship_order_id);
        $this->assertSame('JNE001', $shipment->waybill_id);
        $this->assertSame('confirmed', $shipment->status);
        $this->assertNotNull($shipment->shipped_at);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/orders')
            && $request['reference_id'] === $order->number
            && $request['courier_company'] === 'jne');
    }

    public function test_booking_a_waybill_twice_is_refused(): void
    {
        Http::fake(['api.biteship.com/v1/orders' => Http::response(['id' => 'x', 'status' => 'confirmed', 'courier' => []], 200)]);

        $order = $this->makeOrderWithQuotedShipment();
        $this->post("/admin/pesanan/{$order->number}/kirim");

        $this->post("/admin/pesanan/{$order->number}/kirim")
            ->assertSessionHas('error');

        Http::assertSentCount(1);
    }

    public function test_shipping_a_pickup_order_that_has_no_shipment_quote_is_refused(): void
    {
        $order = Order::factory()->pickup()->create(['status' => 'diproses']);

        $this->post("/admin/pesanan/{$order->number}/kirim")
            ->assertSessionHas('error');
    }

    /**
     * "Cek Status Kirim" — the shipment side's equivalent of the payment
     * side's "Cek Status" (`PaymentReconciliationTest`): a manual nudge for
     * a webhook that's late, lost, or (in local development, where Biteship
     * can't reach `localhost`) was never going to arrive at all.
     */
    public function test_checking_shipment_status_applies_a_delivered_update_and_completes_the_order(): void
    {
        $order = $this->makeOrderWithQuotedShipment();
        $order->shipment->update(Shipment::factory()->booked()->make()->only([
            'biteship_order_id', 'tracking_id', 'waybill_id', 'courier_link', 'status', 'shipped_at',
        ]));
        $order->update(['status' => 'dikirim']);

        Http::fake(['api.biteship.com/v1/trackings/*' => Http::response([
            'status' => 'delivered',
            'waybill_id' => 'JNE999',
        ], 200)]);

        $this->post("/admin/pesanan/{$order->number}/cek-status-kirim")
            ->assertRedirect()
            ->assertSessionHas('success');

        $shipment = $order->shipment->fresh();
        $this->assertSame('delivered', $shipment->status);
        $this->assertSame('JNE999', $shipment->waybill_id);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_checking_shipment_status_is_refused_when_nothing_has_been_booked_yet(): void
    {
        $order = $this->makeOrderWithQuotedShipment();

        $this->post("/admin/pesanan/{$order->number}/cek-status-kirim")
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_checking_shipment_status_reports_a_biteship_failure_without_changing_anything(): void
    {
        $order = $this->makeOrderWithQuotedShipment();
        $order->shipment->update(Shipment::factory()->booked()->make()->only([
            'biteship_order_id', 'tracking_id', 'waybill_id', 'courier_link', 'status', 'shipped_at',
        ]));

        Http::fake(['api.biteship.com/v1/trackings/*' => Http::response(['error' => 'not found'], 404)]);

        $this->post("/admin/pesanan/{$order->number}/cek-status-kirim")
            ->assertSessionHas('error');

        $this->assertSame('confirmed', $order->shipment->fresh()->status);
    }
}

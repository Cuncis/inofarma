<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * "Detail Pesanan" (`ui.pesanan.show`) — items, total, and the Bayar/Batalkan
 * actions. Split from Lacak Pesanan (`ui.track-order`, `OrderHistoryTest`),
 * which is a read-only shipment/pickup timeline with no actions of its own.
 */
class OrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingOrder(Customer $customer): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id, 'fulfilment' => 'ambil',
            'status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar',
            'payment_method' => 'online', 'grand_total' => 30000, 'expires_at' => now()->addDay(),
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 30000, 'quantity' => 1, 'line_total' => 30000, 'batches_consumed' => $manifest,
        ]);

        return $order->fresh();
    }

    public function test_the_detail_page_carries_items_total_and_pay_action(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makePendingOrder($customer);
        $this->actingAs($customer, 'customer');

        $this->get("/ui/pesanan/{$order->number}")->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/OrderDetail')
            ->where('order.number', $order->number)
            ->where('order.total', 30000)
            ->has('order.items', 1)
            ->where('order.canPay', true)
            ->where('order.isCancellable', true)
        );
    }

    public function test_a_customer_can_pay_and_cancel_from_the_detail_page(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makePendingOrder($customer);
        $this->actingAs($customer, 'customer');

        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        Http::fake(['api-sandbox.doku.com/*' => Http::response([
            'response' => ['payment' => ['token_id' => 'tok_x', 'url' => 'https://sandbox.doku.com/checkout-link-v2/tok_x']],
        ], 200)]);

        $this->post("/ui/pesanan/{$order->number}/bayar")
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/tok_x');

        $this->post("/ui/pesanan/{$order->number}/batalkan")
            ->assertSessionHas('success');

        $this->assertSame('dibatalkan', $order->fresh()->status);
    }

    public function test_a_customer_cannot_view_another_customers_order_detail(): void
    {
        $owner = Customer::factory()->create(['status' => 'aktif']);
        $intruder = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makePendingOrder($owner);

        $this->actingAs($intruder, 'customer');
        $this->get("/ui/pesanan/{$order->number}")->assertNotFound();
    }

    public function test_the_doku_checkout_session_points_its_callback_at_the_detail_page(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makePendingOrder($customer);
        $this->actingAs($customer, 'customer');

        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        Http::fake(['api-sandbox.doku.com/*' => Http::response([
            'response' => ['payment' => ['token_id' => 'tok_y', 'url' => 'https://sandbox.doku.com/checkout-link-v2/tok_y']],
        ], 200)]);

        $this->post("/ui/pesanan/{$order->number}/bayar");

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api-sandbox.doku.com')
            && str_contains($request['order']['callback_url'], "/ui/pesanan/{$order->number}")
            && ! str_contains($request['order']['callback_url'], 'track-order'));
    }
}

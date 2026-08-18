<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function placeOrder(Customer $customer, Branch $branch, Product $product): string
    {
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code]);
        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini']);

        return $customer->orders()->latest('id')->value('number');
    }

    public function test_the_history_list_is_empty_for_a_new_customer(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        $this->get('/ui/order-history')->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/OrderHistory')
            ->has('orders', 0)
        );
    }

    public function test_a_placed_order_appears_in_the_history(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true, 'name' => 'Inofarma Otista']);
        $product = Product::factory()->create(['name' => 'Vitamin C 1000mg']);

        $this->actingAs($customer, 'customer');
        $number = $this->placeOrder($customer, $branch, $product);

        $this->get('/ui/order-history')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('orders', 1)
            ->where('orders.0.number', $number)
            ->where('orders.0.status', 'Menunggu Pembayaran')
            ->where('orders.0.branchName', 'Inofarma Otista')
        );
    }

    public function test_the_tracking_timeline_advances_as_the_status_changes(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();

        $this->actingAs($customer, 'customer');
        $number = $this->placeOrder($customer, $branch, $product);
        $order = $customer->orders()->where('number', $number)->first();

        $this->get("/ui/track-order/{$number}")->assertInertia(fn (AssertableInertia $page) => $page
            ->where('order.steps.0.state', 'current')
            ->where('order.steps.1.state', 'pending')
        );

        $order->update(['status' => 'siap diambil', 'ready_at' => now()]);

        $this->get("/ui/track-order/{$number}")->assertInertia(fn (AssertableInertia $page) => $page
            ->where('order.steps.0.state', 'done')
            ->where('order.steps.1.state', 'done')
            ->where('order.steps.2.state', 'current')
            ->where('order.steps.2.label', 'Siap Diambil')
        );

        $order->update(['status' => 'selesai', 'completed_at' => now()]);

        $this->get("/ui/track-order/{$number}")->assertInertia(fn (AssertableInertia $page) => $page
            ->where('order.steps.3.state', 'done')
            ->where('order.steps.3.label', 'Diambil')
            ->where('order.isCancellable', false)
        );
    }

    public function test_a_cancelled_orders_timeline_is_a_banner_instead_of_steps(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();

        $this->actingAs($customer, 'customer');
        $number = $this->placeOrder($customer, $branch, $product);

        $this->post("/ui/pesanan/{$number}/batalkan");

        $this->get("/ui/track-order/{$number}")->assertInertia(fn (AssertableInertia $page) => $page
            ->where('order.status', 'Dibatalkan')
            ->has('order.steps', 0)
        );
    }
}

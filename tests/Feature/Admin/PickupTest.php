<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * ROADMAP.md 7.2: the pickup code (+ QR) issued when an order is staged, and
 * the counter's hand-over screen that consumes it.
 */
class PickupTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function makeReadyableOrder(): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->pickup()->create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id, 'status' => 'diproses',
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 10000, 'quantity' => 1, 'line_total' => 10000, 'batches_consumed' => $manifest,
        ]);

        return $order->fresh();
    }

    public function test_marking_an_order_ready_issues_a_six_digit_code_and_expiry(): void
    {
        $order = $this->makeReadyableOrder();

        $this->post("/admin/pesanan/{$order->number}/siap")
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('siap diambil', $order->status);
        $this->assertNotNull($order->ready_at);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $order->pickup_code);
        $this->assertTrue($order->pickup_code_expires_at->greaterThan(now()->addHours(47)));
    }

    public function test_a_delivery_order_has_no_pickup_code_to_issue(): void
    {
        $order = Order::factory()->create(['status' => 'diproses', 'fulfilment' => 'antar']);

        $this->post("/admin/pesanan/{$order->number}/siap")
            ->assertSessionHas('error');

        $this->assertNull($order->fresh()->pickup_code);
    }

    public function test_the_pickup_queue_lists_ready_orders_and_prefills_from_the_qr_url(): void
    {
        $order = $this->makeReadyableOrder();
        $this->post("/admin/pesanan/{$order->number}/siap");
        $code = $order->fresh()->pickup_code;

        $this->get("/admin/pengambilan?order={$order->number}&kode={$code}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/PickupQueue')
                ->has('orders', 1)
                ->where('prefill.order', $order->number)
                ->where('prefill.code', $code)
            );
    }

    public function test_the_correct_code_hands_the_order_over_and_logs_who_did_it(): void
    {
        $order = $this->makeReadyableOrder();
        $this->post("/admin/pesanan/{$order->number}/siap");
        $code = $order->fresh()->pickup_code;

        $this->post("/admin/pengambilan/{$order->number}/serahkan", ['code' => $code])
            ->assertRedirect(route('admin.pengambilan.index'))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('selesai', $order->status);
        $this->assertNotNull($order->picked_up_at);
        $this->assertNotNull($order->completed_at);
        $this->assertNotNull($order->handed_over_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'pesanan.serahkan',
            'auditable_id' => $order->id,
        ]);
    }

    public function test_the_wrong_code_is_refused_and_does_not_complete_the_order(): void
    {
        $order = $this->makeReadyableOrder();
        $this->post("/admin/pesanan/{$order->number}/siap");

        $this->post("/admin/pengambilan/{$order->number}/serahkan", ['code' => '000000'])
            ->assertSessionHas('error');

        $this->assertSame('siap diambil', $order->fresh()->status);
    }

    public function test_an_expired_code_is_refused(): void
    {
        $order = $this->makeReadyableOrder();
        $this->post("/admin/pesanan/{$order->number}/siap");
        $order->refresh();
        $order->update(['pickup_code_expires_at' => now()->subMinute()]);

        $this->post("/admin/pengambilan/{$order->number}/serahkan", ['code' => $order->pickup_code])
            ->assertSessionHas('error');

        $this->assertSame('siap diambil', $order->fresh()->status);
    }

    public function test_an_already_picked_up_order_cannot_be_handed_over_again(): void
    {
        $order = $this->makeReadyableOrder();
        $this->post("/admin/pesanan/{$order->number}/siap");
        $code = $order->fresh()->pickup_code;
        $this->post("/admin/pengambilan/{$order->number}/serahkan", ['code' => $code]);

        $this->post("/admin/pengambilan/{$order->number}/serahkan", ['code' => $code])
            ->assertSessionHas('error');
    }
}

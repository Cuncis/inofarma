<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `pesanan:kadaluwarsakan-pengambilan` — the pickup-window half of
 * ROADMAP.md Fase 7.2, same shape as `ExpireUnpaidOrdersTest` for Fase 6's
 * payment window.
 */
class ExpireUnclaimedPickupsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->pickup()->create(array_merge([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'status' => 'siap diambil', 'pickup_code' => '123456',
            'pickup_code_expires_at' => now()->subHour(),
        ], $overrides));

        $manifest = (new StockAllocator)->consume($branch, $product, 2, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 10000, 'quantity' => 2, 'line_total' => 20000, 'batches_consumed' => $manifest,
        ]);

        return $order;
    }

    public function test_a_ready_order_past_its_pickup_window_is_marked_kedaluwarsa_and_stock_returns(): void
    {
        $order = $this->makeOrder();
        $product = $order->items->first()->product;
        $branch = $order->branch;
        $this->assertSame(8, $product->stockAt($branch)->fresh()->quantity);

        $this->artisan('pesanan:kadaluwarsakan-pengambilan')->assertSuccessful();

        $order->refresh();
        $this->assertSame('kedaluwarsa', $order->status);
        $this->assertSame(10, $product->stockAt($branch)->fresh()->quantity);
    }

    public function test_an_order_still_within_its_pickup_window_is_left_alone(): void
    {
        $order = $this->makeOrder(['pickup_code_expires_at' => now()->addHours(2)]);

        $this->artisan('pesanan:kadaluwarsakan-pengambilan');

        $this->assertSame('siap diambil', $order->fresh()->status);
    }

    public function test_an_already_picked_up_order_is_left_alone_even_past_the_window(): void
    {
        $order = $this->makeOrder(['status' => 'selesai', 'picked_up_at' => now()->subMinutes(5)]);

        $this->artisan('pesanan:kadaluwarsakan-pengambilan');

        $this->assertSame('selesai', $order->fresh()->status);
    }
}

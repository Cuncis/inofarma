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
 * `pesanan:kadaluwarsakan` — the 24-hour payment window ROADMAP.md Fase 6
 * asks for, run as a scheduled sweep rather than only reacting to DOKU's own
 * EXPIRED notification (which never arrives for an order that never opened a
 * DOKU session in the first place).
 */
class ExpireUnpaidOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create(array_merge([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar',
            'expires_at' => now()->subHour(),
        ], $overrides));

        $manifest = (new StockAllocator)->consume($branch, $product, 2, 'penjualan', $order);

        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 10000, 'quantity' => 2, 'line_total' => 20000, 'batches_consumed' => $manifest,
        ]);

        return $order;
    }

    public function test_an_unpaid_order_past_its_expiry_is_marked_kedaluwarsa_and_stock_returns(): void
    {
        $order = $this->makeOrder();
        $product = $order->items->first()->product;
        $branch = $order->branch;
        $this->assertSame(8, $product->stockAt($branch)->fresh()->quantity);

        $this->artisan('pesanan:kadaluwarsakan')->assertSuccessful();

        $order->refresh();
        $this->assertSame('kedaluwarsa', $order->status);
        $this->assertSame(10, $product->stockAt($branch)->fresh()->quantity);
    }

    public function test_a_paid_order_past_its_expiry_is_left_alone(): void
    {
        $order = $this->makeOrder(['payment_status' => 'lunas', 'status' => 'diproses']);

        $this->artisan('pesanan:kadaluwarsakan');

        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_an_order_still_within_its_window_is_left_alone(): void
    {
        $order = $this->makeOrder(['expires_at' => now()->addHours(2)]);

        $this->artisan('pesanan:kadaluwarsakan');

        $this->assertSame('menunggu pembayaran', $order->fresh()->status);
    }
}

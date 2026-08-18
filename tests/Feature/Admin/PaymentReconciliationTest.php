<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function makePaidOrder(?Branch $branch = null, ?Carbon $paidAt = null): Order
    {
        $branch ??= Branch::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'status' => 'diproses', 'payment_status' => 'lunas',
            'grand_total' => 75000, 'paid_at' => $paidAt ?? now(),
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 75000, 'quantity' => 1, 'line_total' => 75000, 'batches_consumed' => $manifest,
        ]);

        Payment::factory()->for($order)->success()->create([
            'invoice_number' => $order->number, 'amount' => 75000,
        ]);

        return $order;
    }

    public function test_paid_orders_are_summed_per_branch_per_day(): void
    {
        $branchA = Branch::factory()->create(['name' => 'Cabang Otista']);
        $branchB = Branch::factory()->create(['name' => 'Cabang Kapten Yusuf']);

        $this->makePaidOrder($branchA);
        $this->makePaidOrder($branchA);
        $this->makePaidOrder($branchB);

        $this->get('/admin/rekonsiliasi')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/PaymentReconciliation')
                ->has('daily', 2)
                ->where('grandTotal', 225000)
            );
    }

    public function test_unpaid_orders_are_excluded_from_reconciliation(): void
    {
        $branch = Branch::factory()->create();
        Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => Customer::factory(),
            'status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar', 'grand_total' => 999999,
        ]);

        $this->get('/admin/rekonsiliasi')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('grandTotal', 0));
    }

    public function test_a_paid_order_can_be_refunded_with_a_note(): void
    {
        $order = $this->makePaidOrder();

        $this->post("/admin/faktur/{$order->number}/refund", ['note' => 'Ditransfer manual 20 Agu 2026'])
            ->assertRedirect(route('admin.faktur.show', $order->number))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('refund', $order->payment_status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertSame('refunded', $payment->status);
        $this->assertSame('Ditransfer manual 20 Agu 2026', $payment->refund_note);
        $this->assertNotNull($payment->refunded_at);
    }

    public function test_refund_requires_a_note(): void
    {
        $order = $this->makePaidOrder();

        $this->post("/admin/faktur/{$order->number}/refund", [])
            ->assertSessionHasErrors('note');

        $this->assertSame('lunas', $order->fresh()->payment_status);
    }

    public function test_an_unpaid_order_cannot_be_refunded(): void
    {
        $branch = Branch::factory()->create();
        $order = Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => Customer::factory(),
            'status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar',
        ]);

        $this->post("/admin/faktur/{$order->number}/refund", ['note' => 'Coba refund'])
            ->assertSessionHas('error');

        $this->assertSame('belum bayar', $order->fresh()->payment_status);
    }

    public function test_the_invoice_detail_screen_carries_its_payment_history(): void
    {
        $order = $this->makePaidOrder();

        $this->get("/admin/faktur/{$order->number}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('invoice.payments', 1)
                ->where('invoice.payments.0.status', 'Success')
                ->where('invoice.isRefundable', true)
            );
    }
}

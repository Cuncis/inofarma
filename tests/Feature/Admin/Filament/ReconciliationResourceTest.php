<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Reconciliations\Pages\ListReconciliations;
use App\Filament\Widgets\DailyReconciliationWidget;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `PaymentReconciliationTest`, exercised through the
 * Filament resource/widget at /admin/beta instead of the legacy Inertia route.
 */
class ReconciliationResourceTest extends TestCase
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

    private function makePendingOrder(): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $customer = Customer::factory()->create();
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

        Payment::factory()->for($order)->create(['invoice_number' => $order->number, 'amount' => 30000]);

        return $order;
    }

    public function test_paid_orders_are_summed_per_branch_per_day(): void
    {
        $branchA = Branch::factory()->create(['name' => 'Cabang Otista']);
        $branchB = Branch::factory()->create(['name' => 'Cabang Kapten Yusuf']);

        $this->makePaidOrder($branchA);
        $this->makePaidOrder($branchA);
        $this->makePaidOrder($branchB);

        Livewire::test(DailyReconciliationWidget::class)
            ->assertSee('Cabang Otista')
            ->assertSee('Cabang Kapten Yusuf')
            ->assertSee(Money::rupiah(225000));
    }

    public function test_unpaid_orders_are_excluded_from_reconciliation(): void
    {
        $branch = Branch::factory()->create();
        Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => Customer::factory(),
            'status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar', 'grand_total' => 999999,
        ]);

        Livewire::test(DailyReconciliationWidget::class)
            ->assertSee('Total periode ini: '.Money::rupiah(0));
    }

    public function test_checking_status_applies_a_now_successful_payment(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        $order = $this->makePendingOrder();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        Http::fake(['api-sandbox.doku.com/*' => Http::response([
            'order' => ['invoice_number' => $order->number, 'amount' => '30000'],
            'transaction' => ['status' => 'SUCCESS'],
            'channel' => ['id' => 'VIRTUAL_ACCOUNT_BCA'],
        ], 200)]);

        Livewire::test(ListReconciliations::class)
            ->callTableAction('cekStatus', $payment);

        $order->refresh();
        $this->assertSame('lunas', $order->payment_status);
        $this->assertSame('VIRTUAL_ACCOUNT_BCA', $order->payment_method);
        $this->assertSame('success', $payment->fresh()->status);
    }

    public function test_checking_status_reports_when_doku_has_nothing_new(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        $order = $this->makePendingOrder();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        Http::fake(['api-sandbox.doku.com/*' => Http::response([
            'order' => ['invoice_number' => $order->number, 'amount' => '30000'],
            'transaction' => ['status' => 'PENDING'],
        ], 200)]);

        Livewire::test(ListReconciliations::class)
            ->callTableAction('cekStatus', $payment);

        $this->assertSame('belum bayar', $order->fresh()->payment_status);
    }

    public function test_checking_status_reports_a_doku_failure_without_changing_anything(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        $order = $this->makePendingOrder();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        Http::fake(['api-sandbox.doku.com/*' => Http::response(['error_messages' => ['Not found']], 404)]);

        Livewire::test(ListReconciliations::class)
            ->callTableAction('cekStatus', $payment);

        $this->assertSame('belum bayar', $order->fresh()->payment_status);
    }

    public function test_a_finished_payment_has_no_check_status_action(): void
    {
        $order = $this->makePaidOrder();
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        Livewire::test(ListReconciliations::class)
            ->assertTableActionHidden('cekStatus', $payment);
    }
}

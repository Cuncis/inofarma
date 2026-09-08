<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Pickups\Pages\ListPickups;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use App\Support\Pickup\PickupCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as the hand-over part of `PickupTest`, exercised through
 * the Filament resource at /admin/beta instead of the legacy Inertia route.
 */
class PickupResourceTest extends TestCase
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

    public function test_the_queue_lists_only_ready_for_pickup_orders(): void
    {
        $ready = $this->makeReadyableOrder();
        PickupCodeService::issue($ready);
        $ready->refresh();

        $notReady = $this->makeReadyableOrder();

        Livewire::test(ListPickups::class)
            ->assertCanSeeTableRecords([$ready])
            ->assertCanNotSeeTableRecords([$notReady]);
    }

    public function test_the_correct_code_hands_the_order_over_and_logs_who_did_it(): void
    {
        $order = $this->makeReadyableOrder();
        PickupCodeService::issue($order);
        $order->refresh();

        Livewire::test(ListPickups::class)
            ->callTableAction('serahkan', $order, data: ['code' => $order->pickup_code]);

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
        PickupCodeService::issue($order);
        $order->refresh();

        Livewire::test(ListPickups::class)
            ->callTableAction('serahkan', $order, data: ['code' => '000000']);

        $this->assertSame('siap diambil', $order->fresh()->status);
    }

    public function test_an_expired_code_is_refused(): void
    {
        $order = $this->makeReadyableOrder();
        PickupCodeService::issue($order);
        $order->refresh();
        $order->update(['pickup_code_expires_at' => now()->subMinute()]);

        Livewire::test(ListPickups::class)
            ->callTableAction('serahkan', $order, data: ['code' => $order->pickup_code]);

        $this->assertSame('siap diambil', $order->fresh()->status);
    }

    public function test_the_queue_url_filters_to_the_scanned_order(): void
    {
        $order = $this->makeReadyableOrder();
        PickupCodeService::issue($order);
        $order->refresh();

        $other = $this->makeReadyableOrder();
        PickupCodeService::issue($other);
        $other->refresh();

        $this->get("/admin/beta/pengambilan?order={$order->number}")
            ->assertOk()
            ->assertSee($order->number)
            ->assertDontSee($other->number);
    }

    public function test_the_hand_over_action_prefills_the_code_from_the_qr_url(): void
    {
        $order = $this->makeReadyableOrder();
        PickupCodeService::issue($order);
        $order->refresh();

        Livewire::test(ListPickups::class)
            ->set('prefillCode', $order->pickup_code)
            ->mountTableAction('serahkan', $order)
            ->assertTableActionDataSet(['code' => $order->pickup_code]);
    }
}

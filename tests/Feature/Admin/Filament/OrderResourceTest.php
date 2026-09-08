<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
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
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `OrderCrudTest`, `ShipmentTest` and the order-facing part
 * of `PickupTest`, exercised through the Filament resource at /admin
 * instead of the legacy Inertia routes.
 */
class OrderResourceTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
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

    public function test_the_list_shows_one_row_per_seeded_order(): void
    {
        Livewire::test(ListOrders::class)
            ->assertCanSeeTableRecords(Order::all());

        $this->assertSame(self::ORDER_COUNT, Order::count());
    }

    public function test_an_order_can_be_created_with_a_generated_number_and_computed_totals(): void
    {
        $customer = Customer::where('email', 'anisa.rahmawati@mail.com')->firstOrFail();
        $branch = Branch::where('code', 'CB-001')->firstOrFail();
        $productA = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'fulfilment' => 'antar',
                'payment_method' => 'GoPay',
                'status' => 'diproses',
                'shipping_total' => 20000,
                'note' => 'Titip di pos satpam.',
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 4],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::where('customer_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertSame('INO-2452', $order->number);
        $this->assertSame('belum bayar', $order->payment_status);
        // 4 * 12500
        $this->assertSame(50000, $order->subtotal);
        $this->assertSame(70000, $order->grand_total);
        $this->assertSame(1, $order->items()->count());
    }

    public function test_a_completed_order_reports_lunas_payment_status(): void
    {
        $customer = Customer::where('email', 'anisa.rahmawati@mail.com')->firstOrFail();
        $branch = Branch::where('code', 'CB-001')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'fulfilment' => 'ambil',
                'payment_method' => 'Tunai',
                'status' => 'selesai',
                'shipping_total' => 0,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::where('customer_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertSame('lunas', $order->payment_status);
    }

    public function test_line_items_snapshot_the_product_name_and_price(): void
    {
        $order = Order::where('number', 'INO-2450')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'fulfilment' => 'antar',
                'payment_method' => 'GoPay',
                'status' => 'diproses',
                'shipping_total' => 0,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product->update(['price' => 99000]);

        $created = Order::latest('id')->firstOrFail();
        $item = $created->items()->firstOrFail();
        $this->assertSame('Paracetamol 500mg', $item->product_name);
        $this->assertSame(12500, $item->unit_price);
    }

    public function test_viewing_an_order_shows_its_items(): void
    {
        $order = Order::with('items')->where('number', 'INO-2450')->firstOrFail();

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertSee($order->number)
            ->assertSee($order->items->first()->product_name);
    }

    public function test_an_unknown_order_is_a_404(): void
    {
        $this->get('/admin/pesanan/999999')->assertNotFound();
    }

    public function test_an_order_can_be_updated(): void
    {
        $order = Order::where('number', 'INO-2450')->firstOrFail();
        $product = Product::where('sku', 'PRD-003')->firstOrFail();

        Livewire::test(EditOrder::class, ['record' => $order->getKey()])
            ->fillForm([
                'status' => 'dikirim',
                'shipping_total' => 30000,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        $this->assertSame('dikirim', $order->status);
        $this->assertSame(1, $order->items()->count());
        // 2 * 75000 + 30000
        $this->assertSame(180000, $order->grand_total);
    }

    public function test_a_completed_order_cannot_be_deleted(): void
    {
        $order = Order::where('number', 'INO-2451')->firstOrFail();
        $this->assertSame('selesai', $order->status);

        Livewire::test(EditOrder::class, ['record' => $order->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($order);
    }

    public function test_an_unfinished_order_can_be_deleted(): void
    {
        $order = Order::where('number', 'INO-2448')->firstOrFail();
        $this->assertNotSame('selesai', $order->status);

        Livewire::test(EditOrder::class, ['record' => $order->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($order);
    }

    public function test_creating_requires_at_least_one_item(): void
    {
        $customer = Customer::where('email', 'anisa.rahmawati@mail.com')->firstOrFail();
        $branch = Branch::where('code', 'CB-001')->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'fulfilment' => 'antar',
                'payment_method' => 'GoPay',
                'status' => 'diproses',
                'shipping_total' => 0,
                'items' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['items']);
    }

    public function test_an_admin_can_book_a_waybill_for_a_quoted_shipment(): void
    {
        config(['services.biteship.api_key' => 'test-key']);
        Http::fake([
            'api.biteship.com/v1/orders' => Http::response([
                'id' => 'biteship-order-1',
                'status' => 'confirmed',
                'courier' => ['tracking_id' => 'BST-1', 'waybill_id' => 'JNE001', 'link' => 'https://biteship.com/t/1'],
            ], 200),
        ]);

        $order = $this->makeOrderWithQuotedShipment();

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionVisible('ship')
            ->callAction('ship');

        $order->refresh();
        $this->assertSame('dikirim', $order->status);
        $this->assertNotNull($order->ready_at);

        $shipment = $order->shipment;
        $this->assertSame('biteship-order-1', $shipment->biteship_order_id);
        $this->assertSame('JNE001', $shipment->waybill_id);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/orders')
            && $request['reference_id'] === $order->number);
    }

    public function test_booking_a_waybill_twice_is_refused(): void
    {
        config(['services.biteship.api_key' => 'test-key']);
        Http::fake(['api.biteship.com/v1/orders' => Http::response(['id' => 'x', 'status' => 'confirmed', 'courier' => []], 200)]);

        $order = $this->makeOrderWithQuotedShipment();
        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])->callAction('ship');

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionHidden('ship');

        Http::assertSentCount(1);
    }

    public function test_shipping_a_pickup_order_that_has_no_shipment_quote_is_refused(): void
    {
        $order = Order::factory()->pickup()->create(['status' => 'diproses']);

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionHidden('ship');
    }

    public function test_checking_shipment_status_applies_a_delivered_update_and_completes_the_order(): void
    {
        config(['services.biteship.api_key' => 'test-key']);
        $order = $this->makeOrderWithQuotedShipment();
        $order->shipment->update(Shipment::factory()->booked()->make()->only([
            'biteship_order_id', 'tracking_id', 'waybill_id', 'courier_link', 'status', 'shipped_at',
        ]));
        $order->update(['status' => 'dikirim']);

        Http::fake(['api.biteship.com/v1/trackings/*' => Http::response([
            'status' => 'delivered',
            'waybill_id' => 'JNE999',
        ], 200)]);

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionVisible('checkShipmentStatus')
            ->callAction('checkShipmentStatus');

        $shipment = $order->shipment->fresh();
        $this->assertSame('delivered', $shipment->status);
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_checking_shipment_status_is_hidden_when_nothing_has_been_booked_yet(): void
    {
        $order = $this->makeOrderWithQuotedShipment();

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionHidden('checkShipmentStatus');
    }

    public function test_marking_an_order_ready_issues_a_six_digit_code_and_expiry(): void
    {
        $order = $this->makeReadyableOrder();

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionVisible('markReady')
            ->callAction('markReady');

        $order->refresh();
        $this->assertSame('siap diambil', $order->status);
        $this->assertNotNull($order->ready_at);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $order->pickup_code);
        $this->assertTrue($order->pickup_code_expires_at->greaterThan(now()->addHours(47)));
    }

    public function test_a_delivery_order_has_no_mark_ready_action(): void
    {
        $order = Order::factory()->create(['status' => 'diproses', 'fulfilment' => 'antar']);

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->assertActionHidden('markReady');
    }
}

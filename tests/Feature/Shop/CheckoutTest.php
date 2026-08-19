<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function stock(Branch $branch, Product $product, int $quantity): BranchStock
    {
        $stock = BranchStock::factory()->for($branch)->for($product)->create(['quantity' => $quantity]);
        InventoryBatch::factory()->for($branch)->for($product)->create([
            'quantity' => $quantity, 'expires_at' => now()->addYear(),
        ]);

        return $stock;
    }

    private function addToCart(Product $product, Branch $branch, int $quantity = 1): void
    {
        $this->post('/ui/keranjang', [
            'productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => $quantity,
        ])->assertSessionHasNoErrors();
    }

    /** Stubs DOKU's create-payment call so "online" checkout never hits the real network. */
    private function fakeDoku(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);

        Http::fake([
            'api-sandbox.doku.com/*' => Http::response([
                'message' => ['SUCCESS'],
                'response' => [
                    'order' => ['amount' => '0', 'invoice_number' => 'x', 'session_id' => 'sess'],
                    'payment' => [
                        'token_id' => 'tok_123',
                        'url' => 'https://sandbox.doku.com/checkout-link-v2/tok_123',
                        'expired_date' => '20260101000000',
                    ],
                ],
            ], 200),
        ]);
    }

    /** Stubs Biteship's courier list + rates call so "antar" checkout never hits the real network. */
    private function fakeBiteship(int $price = 12000): void
    {
        config(['services.biteship.api_key' => 'test-key']);

        Http::fake([
            'api.biteship.com/v1/couriers' => Http::response([
                'couriers' => [['courier_code' => 'jne', 'courier_name' => 'JNE']],
            ], 200),
            'api.biteship.com/v1/rates/couriers' => Http::response([
                'pricing' => [[
                    'company' => 'jne', 'type' => 'reg',
                    'courier_name' => 'JNE', 'courier_service_name' => 'REG',
                    'price' => $price, 'duration' => '2-3 hari',
                ]],
            ], 200),
        ]);
    }

    /** @return array{courierCompany: string, courierType: string} */
    private function courierChoice(): array
    {
        return ['courierCompany' => 'jne', 'courierType' => 'reg'];
    }

    /**
     * The roadmap's "selesai bila": a customer completes two orders — one
     * delivered, one picked up — from different branches, with no admin
     * involvement.
     */
    public function test_a_customer_can_complete_a_delivery_order_and_a_pickup_order_from_different_branches(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        // Order 1: antar.
        $deliveryBranch = Branch::factory()->create(['supports_delivery' => true, 'supports_pickup' => false]);
        $product1 = Product::factory()->create(['price' => 20000]);
        $this->stock($deliveryBranch, $product1, 10);
        $address = CustomerAddress::factory()->for($customer)->create([
            'latitude' => $deliveryBranch->latitude, 'longitude' => $deliveryBranch->longitude,
        ]);

        $this->addToCart($product1, $deliveryBranch, 2);
        $this->post('/ui/shipping-details', ['addressId' => $address->id])->assertSessionHasNoErrors();

        $this->fakeDoku();
        $this->fakeBiteship(12000);

        // `Inertia::location()` degrades to a plain redirect for a non-Inertia
        // request (exactly what this is) and a 409 + `X-Inertia-Location` for
        // a real Inertia XHR — either way, the browser ends up at DOKU's URL.
        $this->post('/ui/checkout', [
            'fulfilment' => 'antar',
            'paymentMethod' => 'online',
            'courier' => $this->courierChoice(),
            'note' => 'Titip di pos satpam',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/tok_123');

        $order1 = $customer->orders()->first();
        $this->assertNotNull($order1);
        $this->assertSame('antar', $order1->fulfilment);
        $this->assertSame(40000, $order1->subtotal);
        $this->assertSame(12000, $order1->shipping_total);
        $this->assertSame(52000, $order1->grand_total);
        $this->assertSame(8, $product1->stockAt($deliveryBranch)->fresh()->quantity);

        $shipment = Shipment::where('order_id', $order1->id)->first();
        $this->assertNotNull($shipment);
        $this->assertSame('jne', $shipment->courier_company);
        $this->assertSame(12000, $shipment->price);
        $this->assertFalse($shipment->is_booked);

        $payment = Payment::where('order_id', $order1->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);
        $this->assertSame($order1->number, $payment->invoice_number);

        // Cart is empty again, ready for a second order from a different branch.
        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page->has('cart.items', 0));

        // Order 2: ambil, from a different branch entirely.
        $pickupBranch = Branch::factory()->create(['supports_delivery' => false, 'supports_pickup' => true]);
        $product2 = Product::factory()->create(['price' => 15000]);
        $this->stock($pickupBranch, $product2, 10);

        $this->addToCart($product2, $pickupBranch, 3);

        $this->post('/ui/checkout', [
            'fulfilment' => 'ambil',
            'paymentMethod' => 'Tunai',
            'pickupEta' => 'Hari ini',
        ])->assertSessionHasNoErrors();

        $orders = $customer->orders()->orderBy('id')->get();
        $this->assertCount(2, $orders);

        $order2 = $orders->last();
        $this->assertSame('ambil', $order2->fulfilment);
        $this->assertSame($pickupBranch->id, $order2->branch_id);
        $this->assertSame(0, $order2->shipping_total);
        $this->assertSame(45000, $order2->grand_total);
        $this->assertSame(7, $product2->stockAt($pickupBranch)->fresh()->quantity);

        // Different branches, different, non-sequential-looking numbers.
        $this->assertNotSame($order1->number, $order2->number);
        $this->assertStringContainsString($deliveryBranch->code, $order1->number);
        $this->assertStringContainsString($pickupBranch->code, $order2->number);
    }

    public function test_checkout_requires_an_address_for_delivery(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);

        $this->post('/ui/checkout', ['fulfilment' => 'antar', 'paymentMethod' => 'online'])
            ->assertSessionHasErrors('address');

        $this->assertSame(0, $customer->orders()->count());
    }

    public function test_delivery_is_refused_outside_the_branchs_radius(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create([
            'supports_delivery' => true, 'delivery_radius_km' => 5,
            'latitude' => -6.2, 'longitude' => 106.8,
        ]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        // Roughly 100km+ away — well outside a 5km radius.
        $address = CustomerAddress::factory()->for($customer)->create(['latitude' => -7.2, 'longitude' => 107.8]);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);
        $this->post('/ui/shipping-details', ['addressId' => $address->id]);

        $this->post('/ui/checkout', ['fulfilment' => 'antar', 'paymentMethod' => 'online'])
            ->assertSessionHasErrors('address');
    }

    public function test_a_branch_that_does_not_support_pickup_refuses_an_ambil_order(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => false, 'supports_delivery' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);

        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('fulfilment');
    }

    public function test_cash_at_pickup_is_not_offered_for_delivery_orders(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $address = CustomerAddress::factory()->for($customer)->create([
            'latitude' => $branch->latitude, 'longitude' => $branch->longitude,
        ]);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);
        $this->post('/ui/shipping-details', ['addressId' => $address->id]);

        $this->post('/ui/checkout', ['fulfilment' => 'antar', 'paymentMethod' => 'Tunai'])
            ->assertSessionHasErrors('paymentMethod');

        $this->assertSame(0, $customer->orders()->count());
    }

    public function test_a_free_shipping_coupon_zeroes_out_delivery_cost(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $product = Product::factory()->create(['price' => 30000]);
        $this->stock($branch, $product, 10);
        $address = CustomerAddress::factory()->for($customer)->create([
            'latitude' => $branch->latitude, 'longitude' => $branch->longitude,
        ]);
        $coupon = Coupon::factory()->create([
            'code' => 'ONGKIR0', 'type' => 'ongkir gratis', 'value' => 0, 'minimum_purchase' => null,
        ]);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);
        $this->post('/ui/keranjang/kupon', ['code' => 'ONGKIR0'])->assertSessionHasNoErrors();
        $this->post('/ui/shipping-details', ['addressId' => $address->id]);

        $this->fakeDoku();
        $this->fakeBiteship();

        $this->post('/ui/checkout', [
            'fulfilment' => 'antar', 'paymentMethod' => 'online', 'courier' => $this->courierChoice(),
        ])->assertSessionHasNoErrors();

        $order = $customer->orders()->first();
        $this->assertSame(0, $order->shipping_total);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_a_coupon_cannot_be_used_twice_by_the_same_customer(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create(['price' => 30000]);
        $this->stock($branch, $product, 10);
        Coupon::factory()->create([
            'code' => 'SEKALI', 'type' => 'nominal', 'value' => 5000, 'minimum_purchase' => null,
        ]);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch);
        $this->post('/ui/keranjang/kupon', ['code' => 'SEKALI'])->assertSessionHasNoErrors();
        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasNoErrors();

        $this->addToCart($product, $branch);
        $this->post('/ui/keranjang/kupon', ['code' => 'SEKALI'])->assertSessionHasErrors('code');
    }

    public function test_checkout_revalidates_stock_and_rejects_an_order_that_no_longer_fits(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $stock = $this->stock($branch, $product, 5);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch, 5);

        // Stock disappears from under the cart between add-to-cart and checkout
        // (an admin adjustment, another order, etc.) — checkout must catch it.
        $stock->update(['quantity' => 2]);
        InventoryBatch::where('branch_id', $branch->id)->where('product_id', $product->id)->update(['quantity' => 2]);

        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(0, $customer->orders()->count());
        $this->assertSame(2, $stock->fresh()->quantity);
    }

    public function test_a_customer_can_cancel_an_order_before_it_is_processed_and_stock_is_returned(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch, 4);
        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini']);

        $order = $customer->orders()->first();
        $this->assertSame(6, $product->stockAt($branch)->fresh()->quantity);

        $this->post("/ui/pesanan/{$order->number}/batalkan")->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('dibatalkan', $order->status);
        $this->assertSame(10, $product->stockAt($branch)->fresh()->quantity);
    }

    public function test_a_processed_order_cannot_be_cancelled_by_the_customer(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->actingAs($customer, 'customer');
        $this->addToCart($product, $branch, 1);
        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini']);

        $order = $customer->orders()->first();
        $order->update(['status' => 'diproses']);

        $this->post("/ui/pesanan/{$order->number}/batalkan")->assertSessionHas('error');

        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_a_customer_cannot_see_or_cancel_another_customers_order(): void
    {
        $owner = Customer::factory()->create(['status' => 'aktif']);
        $intruder = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->actingAs($owner, 'customer');
        $this->addToCart($product, $branch, 1);
        $this->post('/ui/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'Tunai', 'pickupEta' => 'Hari ini']);
        $order = $owner->orders()->first();

        $this->actingAs($intruder, 'customer');
        $this->get("/ui/track-order/{$order->number}")->assertNotFound();
        $this->post("/ui/pesanan/{$order->number}/batalkan")->assertNotFound();
    }
}

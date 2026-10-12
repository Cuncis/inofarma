<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
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
        $this->post('/keranjang', [
            'productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => $quantity,
        ])->assertSessionHasNoErrors();
    }

    private function makeRegionChain(): array
    {
        $province = Region::factory()->create(['code' => '31', 'parent_code' => null, 'level' => 1, 'name' => 'DKI Jakarta']);
        $regency = Region::factory()->create(['code' => '31.71', 'parent_code' => '31', 'level' => 2, 'name' => 'Jakarta Pusat']);
        $district = Region::factory()->create(['code' => '31.71.01', 'parent_code' => '31.71', 'level' => 3, 'name' => 'Gambir']);
        $village = Region::factory()->create([
            'code' => '31.71.01.1001', 'parent_code' => '31.71.01', 'level' => 4,
            'name' => 'Gambir', 'postal_code' => '10110',
        ]);

        return compact('province', 'regency', 'district', 'village');
    }

    private function fakeDoku(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);

        Http::fake([
            'api-sandbox.doku.com/*' => Http::response([
                'message' => ['SUCCESS'],
                'response' => [
                    'order' => ['amount' => '0', 'invoice_number' => 'x', 'session_id' => 'sess'],
                    'payment' => ['token_id' => 'tok', 'url' => 'https://sandbox.doku.com/checkout-link-v2/tok', 'expired_date' => '20260101000000'],
                ],
            ], 200),
            'api.biteship.com/v1/couriers' => Http::response(['couriers' => [['courier_code' => 'jne', 'courier_name' => 'JNE']]], 200),
            'api.biteship.com/v1/rates/couriers' => Http::response(['pricing' => [[
                'company' => 'jne', 'type' => 'reg', 'courier_name' => 'JNE', 'courier_service_name' => 'REG', 'price' => 12000, 'duration' => '2-3 hari',
            ]]], 200),
        ]);
        config(['services.biteship.api_key' => 'test-key']);
    }

    /** @return array<string, mixed> */
    private function guestDetails(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pembeli Tamu',
            'phone' => '081234567890',
            'email' => 'tamu@example.test',
            'consent' => true,
            'addressLine' => 'Jl. Medan Merdeka No. 1',
            'kelurahan' => 'Gambir',
            'kecamatan' => 'Gambir',
            'kota' => 'Jakarta Pusat',
            'provinsi' => 'DKI Jakarta',
            'postalCode' => '10110',
            'latitude' => null,
            'longitude' => null,
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function pickup(Branch $branch, array $overrides = []): array
    {
        return $this->guestDetails([
            'fulfilment' => 'ambil', 'paymentMethod' => 'online',
            'pickupBranchId' => $branch->code, 'pickupEta' => 'Hari ini',
            ...$overrides,
        ]);
    }

    public function test_a_guest_sees_the_whole_checkout_on_one_page(): void
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);
        $this->makeRegionChain();

        $this->get('/checkout')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/Checkout')
                ->where('isGuest', true)
                ->where('cart.address', null)
                ->has('provinces', 1)
                ->has('pickupBranches', 1)
            );
    }

    public function test_the_old_guest_checkout_address_goes_to_the_single_checkout(): void
    {
        $this->get('/checkout/tamu')->assertRedirect(route('ui.checkout'));
    }

    public function test_an_empty_cart_goes_back_to_the_cart(): void
    {
        $this->get('/checkout')->assertRedirect(route('ui.cart'));
        $this->post('/checkout', $this->guestDetails(['fulfilment' => 'ambil', 'paymentMethod' => 'online']))
            ->assertRedirect(route('ui.cart'));

        $this->assertSame(0, Customer::count());
    }

    public function test_a_guest_pickup_order_creates_the_account_saves_the_address_and_goes_to_payment(): void
    {
        Notification::fake();
        $this->fakeDoku();

        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create(['price' => 25000]);
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch, 2);
        $this->makeRegionChain();

        $this->withHeaders(['X-Inertia' => 'true'])
            ->post('/checkout', $this->pickup($branch))
            ->assertSessionHasNoErrors()
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://sandbox.doku.com/checkout-link-v2/tok');

        $this->assertTrue(Auth::guard('customer')->check());

        $customer = Customer::where('email', 'tamu@example.test')->firstOrFail();
        $this->assertSame('Pembeli Tamu', $customer->name);
        $this->assertSame('081234567890', $customer->phone);
        $this->assertNotNull($customer->consent_at);

        $address = $customer->addresses()->firstOrFail();
        $this->assertTrue($address->is_default);
        $this->assertSame('Jl. Medan Merdeka No. 1', $address->address_line);
        $this->assertSame('10110', $address->postal_code);

        $order = $customer->orders()->firstOrFail();
        $this->assertSame('ambil', $order->fulfilment);
        $this->assertSame($branch->id, $order->branch_id);
        $this->assertSame(50000, $order->subtotal);
    }

    public function test_a_guest_can_pick_up_without_typing_an_address(): void
    {
        Notification::fake();
        $this->fakeDoku();

        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/checkout', $this->pickup($branch, [
            'addressLine' => '', 'kota' => '', 'provinsi' => '', 'kelurahan' => '', 'kecamatan' => '', 'postalCode' => '',
        ]))->assertSessionHasNoErrors();

        $customer = Customer::where('email', 'tamu@example.test')->firstOrFail();
        $this->assertSame(0, $customer->addresses()->count());
        $this->assertSame(1, $customer->orders()->count());
    }

    public function test_a_guest_delivery_order_uses_the_address_typed_on_the_same_page(): void
    {
        Notification::fake();
        $this->fakeDoku();

        $branch = Branch::factory()->create([
            'supports_delivery' => true, 'supports_pickup' => true, 'latitude' => -6.2, 'longitude' => 106.8,
        ]);
        $product = Product::factory()->create(['price' => 25000]);
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/checkout', $this->guestDetails([
            'fulfilment' => 'antar', 'paymentMethod' => 'online',
            'courier' => ['courierCompany' => 'jne', 'courierType' => 'reg'],
            'latitude' => -6.21, 'longitude' => 106.81,
        ]))->assertSessionHasNoErrors();

        $order = Customer::where('email', 'tamu@example.test')->firstOrFail()->orders()->firstOrFail();
        $this->assertSame('antar', $order->fulfilment);
        $this->assertSame(12000, $order->shipping_total);
        $this->assertStringContainsString('Jl. Medan Merdeka No. 1', $order->shipping_address);
    }

    public function test_the_shipping_quote_works_for_an_address_not_saved_yet(): void
    {
        $this->fakeDoku();

        $branch = Branch::factory()->create(['supports_delivery' => true, 'latitude' => -6.2, 'longitude' => 106.8]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->getJson('/checkout/ongkir?postalCode=10110&latitude=-6.21&longitude=106.81')
            ->assertOk()
            ->assertJsonPath('options.0.courierCompany', 'jne')
            ->assertJsonPath('options.0.price', 12000);

        $this->getJson('/checkout/ongkir')->assertOk()->assertExactJson(['options' => []]);
    }

    public function test_an_email_that_is_already_registered_is_refused(): void
    {
        Customer::factory()->create(['email' => 'sudah@example.test']);

        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/checkout', $this->pickup($branch, ['email' => 'sudah@example.test']))
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('customer')->check());
        $this->assertSame(1, Customer::where('email', 'sudah@example.test')->count());
    }

    public function test_a_guest_must_give_contact_details_and_consent(): void
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/checkout', $this->pickup($branch, ['consent' => false, 'name' => '', 'phone' => '', 'email' => '']))
            ->assertSessionHasErrors(['consent', 'name', 'phone', 'email']);

        $this->assertSame(0, Customer::count());
    }

    public function test_a_guest_delivery_order_needs_an_address_and_creates_no_account_without_one(): void
    {
        $branch = Branch::factory()->create(['supports_delivery' => true, 'latitude' => -6.2, 'longitude' => 106.8]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/checkout', $this->guestDetails([
            'fulfilment' => 'antar', 'paymentMethod' => 'online', 'kota' => '', 'provinsi' => '', 'addressLine' => '',
        ]))->assertSessionHasErrors(['addressLine', 'kota', 'provinsi']);

        $this->assertSame(0, Customer::count());
    }
}

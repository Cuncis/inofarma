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
        $this->post('/ui/keranjang', [
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

    public function test_a_guest_can_reach_the_guest_checkout_form_with_items_in_the_cart(): void
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);
        $this->makeRegionChain();

        $this->get('/ui/checkout/tamu')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/GuestCheckout')
                ->has('provinces', 1)
            );
    }

    public function test_guest_checkout_redirects_to_cart_when_empty(): void
    {
        $this->get('/ui/checkout/tamu')->assertRedirect(route('ui.cart'));
    }

    public function test_an_already_signed_in_customer_is_bounced_to_the_real_checkout(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        $this->get('/ui/checkout/tamu')->assertRedirect(route('ui.checkout'));
    }

    public function test_submitting_guest_details_creates_an_account_signs_in_and_reaches_checkout(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create(['supports_pickup' => true, 'supports_delivery' => true]);
        $product = Product::factory()->create(['price' => 25000]);
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch, 2);
        $this->makeRegionChain();

        $this->post('/ui/checkout/tamu', $this->guestDetails())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('ui.checkout'));

        $this->assertTrue(Auth::guard('customer')->check());

        $customer = Customer::where('email', 'tamu@example.test')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Pembeli Tamu', $customer->name);
        $this->assertSame('081234567890', $customer->phone);
        $this->assertNotNull($customer->consent_at);
        $this->assertSame($customer->id, Auth::guard('customer')->id());

        $address = $customer->addresses()->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
        $this->assertSame('Jl. Medan Merdeka No. 1', $address->address_line);
        $this->assertSame('10110', $address->postal_code);

        // The session cart from before signing in becomes this new
        // customer's real cart — nothing was lost by not having an account
        // yet when the product was added.
        $this->assertSame(2, $customer->cart->items()->where('product_id', $product->id)->value('quantity'));

        // Checkout now behaves exactly like any other signed-in customer's —
        // the address just created is already attached to the cart.
        $this->get('/ui/checkout')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('cart.address.id', $address->id)
            );
    }

    public function test_guest_checkout_refuses_an_email_that_is_already_registered(): void
    {
        Customer::factory()->create(['email' => 'sudah@example.test']);

        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);
        $this->makeRegionChain();

        $this->post('/ui/checkout/tamu', $this->guestDetails(['email' => 'sudah@example.test']))
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('customer')->check());
        $this->assertSame(1, Customer::where('email', 'sudah@example.test')->count());
    }

    public function test_guest_checkout_requires_consent_and_an_address(): void
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $this->addToCart($product, $branch);

        $this->post('/ui/checkout/tamu', $this->guestDetails(['consent' => false, 'kota' => '', 'provinsi' => '']))
            ->assertSessionHasErrors(['consent', 'kota', 'provinsi']);

        $this->assertSame(0, Customer::count());
    }

    public function test_guest_checkout_store_redirects_to_cart_when_empty(): void
    {
        $this->post('/ui/checkout/tamu', $this->guestDetails())->assertRedirect(route('ui.cart'));

        $this->assertSame(0, Customer::count());
    }
}

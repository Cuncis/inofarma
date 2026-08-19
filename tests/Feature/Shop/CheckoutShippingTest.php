<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * `GET /ui/checkout/ongkir` — the live Biteship quote list the Checkout
 * screen's courier picker renders, and the server-side re-quote
 * `CheckoutController::store()` always trusts instead of whatever the
 * client submitted.
 */
class CheckoutShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.biteship.api_key' => 'test-key']);
    }

    private function fakeBiteshipRates(): void
    {
        Http::fake([
            'api.biteship.com/v1/couriers' => Http::response([
                'couriers' => [
                    ['courier_code' => 'jne', 'courier_name' => 'JNE'],
                    ['courier_code' => 'gojek', 'courier_name' => 'Gojek'],
                ],
            ], 200),
            'api.biteship.com/v1/rates/couriers' => Http::response([
                'pricing' => [
                    ['company' => 'jne', 'type' => 'reg', 'courier_name' => 'JNE', 'courier_service_name' => 'REG', 'price' => 15000],
                    ['company' => 'gojek', 'type' => 'instant', 'courier_name' => 'Gojek', 'courier_service_name' => 'Instant', 'price' => 22000],
                ],
            ], 200),
        ]);
    }

    private function prepareCartWithAddress(): CustomerAddress
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);
        $address = CustomerAddress::factory()->for($customer)->create([
            'latitude' => $branch->latitude, 'longitude' => $branch->longitude,
        ]);

        $this->actingAs($customer, 'customer');
        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1]);
        $this->post('/ui/shipping-details', ['addressId' => $address->id]);

        return $address;
    }

    public function test_it_returns_the_cheapest_first_from_a_real_rates_call(): void
    {
        $this->fakeBiteshipRates();
        $this->prepareCartWithAddress();

        $response = $this->getJson('/ui/checkout/ongkir')->assertOk();

        $options = $response->json('options');
        $this->assertCount(2, $options);
        $this->assertSame('jne', $options[0]['courierCompany']);
        $this->assertSame(15000, $options[0]['price']);
        $this->assertSame('gojek', $options[1]['courierCompany']);
    }

    public function test_it_returns_no_options_without_an_address_selected(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true]);
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $this->actingAs($customer, 'customer');
        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1]);

        $this->getJson('/ui/checkout/ongkir')->assertOk()->assertJson(['options' => []]);
    }

    public function test_checkout_rejects_a_courier_the_live_rates_call_no_longer_returns(): void
    {
        $this->fakeBiteshipRates();
        $this->prepareCartWithAddress();

        $this->post('/ui/checkout', [
            'fulfilment' => 'antar',
            'paymentMethod' => 'online',
            // Client claims a courier that was never actually quoted — the
            // kind of tampering a modified request body could attempt.
            'courier' => ['courierCompany' => 'anteraja', 'courierType' => 'reg'],
        ])->assertSessionHasErrors('courier');
    }
}

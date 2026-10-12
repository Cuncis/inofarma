<?php

namespace Tests\Feature\Shop;

use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Support\Shipping\ShippingQuoteService;
use App\Support\Shipping\ShippingUnavailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ShippingPreflightTest extends TestCase
{
    use RefreshDatabase;

    private function fakeBiteship(): void
    {
        config(['services.biteship.api_key' => 'test-key']);

        Http::fake([
            'api.biteship.com/v1/couriers' => Http::response(['couriers' => [
                ['courier_code' => 'jne', 'courier_service_code' => 'reg'],
                ['courier_code' => 'jne', 'courier_service_code' => 'yes'],
                ['courier_code' => 'gojek', 'courier_service_code' => 'instant'],
                ['courier_code' => 'jne', 'courier_service_code' => 'jtr'],
            ]], 200),
            'api.biteship.com/v1/rates/couriers' => Http::response(['pricing' => [[
                'company' => 'jne', 'type' => 'reg', 'courier_name' => 'JNE', 'courier_service_name' => 'REG', 'price' => 12000, 'duration' => '2-3 hari',
            ]]], 200),
            'api.biteship.com/v1/orders' => Http::response(['id' => 'x', 'status' => 'confirmed', 'courier' => []], 200),
        ]);
    }

    /**
     * @return array{Customer, Branch, CustomerAddress, Product}
     */
    private function deliveryCart(array $branch = [], array $address = []): array
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_delivery' => true, ...$branch]);
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);
        $address = CustomerAddress::factory()->for($customer)->create($address ?: ['latitude' => -6.3, 'longitude' => 106.83]);

        $this->actingAs($customer, 'customer');
        $this->post('/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1])->assertSessionHasNoErrors();
        $this->post('/shipping-details', ['addressId' => $address->id]);

        return [$customer, $branch, $address, $product];
    }

    public function test_a_branch_without_coordinates_is_reported_before_biteship_is_called(): void
    {
        $this->fakeBiteship();
        [, $branch, $address, $product] = $this->deliveryCart(['latitude' => null, 'longitude' => null]);

        try {
            ShippingQuoteService::make()->quote($branch, $address, [['product' => $product, 'quantity' => 1]]);
            $this->fail('Expected ShippingUnavailableException.');
        } catch (ShippingUnavailableException $exception) {
            $this->assertStringContainsString('Koordinat', $exception->getMessage());
            $this->assertStringContainsString($branch->name, $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_an_address_without_a_map_point_is_reported_before_biteship_is_called(): void
    {
        $this->fakeBiteship();
        [, $branch, , $product] = $this->deliveryCart(['latitude' => -6.2, 'longitude' => 106.8]);
        $pinless = CustomerAddress::factory()->create(['latitude' => null, 'longitude' => null]);

        $this->expectException(ShippingUnavailableException::class);

        try {
            ShippingQuoteService::make()->quote($branch, $pinless, [['product' => $product, 'quantity' => 1]]);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_the_rates_endpoint_tells_the_shopper_what_is_wrong_instead_of_a_generic_error(): void
    {
        $this->fakeBiteship();
        $this->deliveryCart(['latitude' => null, 'longitude' => null]);

        $this->getJson('/checkout/ongkir')
            ->assertStatus(422)
            ->assertJsonPath('options', [])
            ->assertJsonPath('error', 'Pengiriman dari cabang ini belum tersedia. Pilih Ambil di toko atau coba lagi nanti.');
    }

    public function test_checkout_refuses_a_delivery_order_when_the_branch_has_no_coordinates(): void
    {
        $this->fakeBiteship();
        [$customer] = $this->deliveryCart(['latitude' => null, 'longitude' => null]);

        $this->post('/checkout', [
            'fulfilment' => 'antar', 'paymentMethod' => 'online',
            'courier' => ['courierCompany' => 'jne', 'courierType' => 'reg'],
        ])->assertSessionHasErrors(['courier' => 'Pengiriman dari cabang ini belum tersedia. Pilih Ambil di toko atau coba lagi nanti.']);

        $this->assertSame(0, Order::where('customer_id', $customer->id)->count());
    }

    public function test_each_courier_company_is_sent_to_biteship_once(): void
    {
        $this->fakeBiteship();
        [, $branch, $address, $product] = $this->deliveryCart(['latitude' => -6.2, 'longitude' => 106.8]);

        ShippingQuoteService::make()->quote($branch, $address, [['product' => $product, 'quantity' => 1]]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/rates/couriers') && $request['couriers'] === 'jne,gojek');
    }

    public function test_a_waybill_is_not_booked_for_a_branch_that_is_not_ready_and_the_admin_is_told_why(): void
    {
        $this->fakeBiteship();
        $this->seed();
        $this->post('/admin/masuk', ['email' => 'admin@inofarma.co.id', 'password' => 'password']);

        $branch = Branch::factory()->create([
            'supports_delivery' => true, 'latitude' => null, 'longitude' => null, 'address_line' => 'Alamat belum diisi (Jengki)',
        ]);
        $order = Order::factory()->create([
            'branch_id' => $branch->id, 'fulfilment' => 'antar', 'status' => 'siap dikirim',
            'recipient_name' => 'Budi', 'recipient_phone' => '0812', 'shipping_address' => 'Jl. Contoh',
            'shipping_latitude' => -6.2, 'shipping_longitude' => 106.8,
        ]);
        Shipment::factory()->for($order)->create();

        Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
            ->callAction('ship')
            ->assertNotified();

        $this->assertSame('siap dikirim', $order->fresh()->status);
        $this->assertFalse($order->shipment->fresh()->is_booked);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/v1/orders'));
    }
}

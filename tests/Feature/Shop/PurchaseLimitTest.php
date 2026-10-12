<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PurchaseLimitTest extends TestCase
{
    use RefreshDatabase;

    private function shopperWithProduct(int $quantity, array $productAttributes = []): array
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create($productAttributes);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 20]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 20, 'expires_at' => now()->addYear()]);

        $this->actingAs($customer, 'customer');
        $this->post('/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => $quantity])
            ->assertSessionHasNoErrors();

        return [$customer, $branch, $product];
    }

    private function fakeDoku(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        Http::fake(['api-sandbox.doku.com/*' => Http::response([
            'message' => ['SUCCESS'],
            'response' => [
                'order' => ['amount' => '0', 'invoice_number' => 'x', 'session_id' => 'sess'],
                'payment' => ['token_id' => 'tok', 'url' => 'https://sandbox.doku.com/checkout-link-v2/tok', 'expired_date' => '20260101000000'],
            ],
        ], 200)]);
    }

    public function test_every_product_is_limited_to_two_unless_it_has_a_lower_limit_of_its_own(): void
    {
        $this->assertSame(2, Product::factory()->make(['max_qty_per_order' => null])->purchaseLimit());
        $this->assertSame(2, Product::factory()->make(['max_qty_per_order' => 5])->purchaseLimit());
        $this->assertSame(2, Product::factory()->make(['max_qty_per_order' => 2])->purchaseLimit());
        $this->assertSame(1, Product::factory()->make(['max_qty_per_order' => 1])->purchaseLimit());
    }

    public function test_the_cart_and_product_data_tell_the_storefront_the_limit(): void
    {
        [, , $product] = $this->shopperWithProduct(3);

        $this->getJson('/keranjang/ringkas')
            ->assertOk()
            ->assertJsonPath('items.0.quantity', 3)
            ->assertJsonPath('items.0.purchaseLimit', 2);

        $this->assertSame(2, $product->purchaseLimit());
    }

    public function test_a_cart_quantity_can_be_raised_past_the_limit(): void
    {
        [, , $product] = $this->shopperWithProduct(2);

        $this->patch("/keranjang/{$product->sku}", ['quantity' => 3])->assertSessionHasNoErrors();

        $this->getJson('/keranjang/ringkas')->assertJsonPath('items.0.quantity', 3);
    }

    public function test_checkout_refuses_an_order_over_the_limit_with_the_message_and_takes_no_stock(): void
    {
        [$customer, $branch, $product] = $this->shopperWithProduct(3);

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors(['quantity' => Product::OVER_LIMIT_MESSAGE]);

        $this->assertSame(0, Order::where('customer_id', $customer->id)->count());
        $this->assertSame(20, $product->stockAt($branch)->fresh()->quantity);
    }

    public function test_checkout_accepts_exactly_the_limit(): void
    {
        [$customer] = $this->shopperWithProduct(2);
        $this->fakeDoku();

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Order::where('customer_id', $customer->id)->count());
    }

    public function test_one_line_over_the_limit_blocks_the_whole_order(): void
    {
        [$customer, $branch] = $this->shopperWithProduct(2);
        $other = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($other)->create(['quantity' => 20]);
        InventoryBatch::factory()->for($branch)->for($other)->create(['quantity' => 20, 'expires_at' => now()->addYear()]);
        $this->post('/keranjang', ['productId' => $other->sku, 'branchId' => $branch->code, 'quantity' => 3])->assertSessionHasNoErrors();

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(0, Order::where('customer_id', $customer->id)->count());
    }

    public function test_opening_checkout_with_a_cart_over_the_limit_sends_the_shopper_back_to_the_cart(): void
    {
        $this->shopperWithProduct(3);

        $this->get('/checkout')
            ->assertRedirect(route('ui.cart'))
            ->assertSessionHas('error', Product::OVER_LIMIT_MESSAGE);
    }

    public function test_opening_checkout_within_the_limit_works(): void
    {
        $this->shopperWithProduct(2);

        $this->get('/checkout')->assertOk();
    }
}

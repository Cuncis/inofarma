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
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PickupCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function stock(Branch $branch, Product $product, int $quantity): void
    {
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => $quantity]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => $quantity, 'expires_at' => now()->addYear()]);
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

    /**
     * A shopper whose cart was started at the delivering branch, with a second
     * branch to collect from.
     *
     * @return array{Customer, Branch, Branch, Product}
     */
    private function shopper(int $quantity = 2): array
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $origin = Branch::factory()->create(['supports_delivery' => true, 'supports_pickup' => true, 'name' => 'Asal']);
        $other = Branch::factory()->create(['supports_delivery' => false, 'supports_pickup' => true, 'name' => 'Lain']);
        $product = Product::factory()->create();
        $this->stock($origin, $product, 10);
        $this->stock($other, $product, 10);

        $this->actingAs($customer, 'customer');
        $this->post('/keranjang', ['productId' => $product->sku, 'branchId' => $origin->code, 'quantity' => $quantity])->assertSessionHasNoErrors();

        return [$customer, $origin, $other, $product];
    }

    public function test_the_order_is_placed_at_the_pickup_branch_the_shopper_chose_at_checkout(): void
    {
        [$customer, $origin, $other, $product] = $this->shopper(2);
        $this->fakeDoku();

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupBranchId' => $other->code, 'pickupEta' => 'Hari ini'])
            ->assertSessionHasNoErrors();

        $order = Order::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($other->id, $order->branch_id);
        $this->assertStringContainsString($other->code, $order->number);
        $this->assertSame(8, $product->stockAt($other)->fresh()->quantity);
        $this->assertSame(10, $product->stockAt($origin)->fresh()->quantity);
    }

    public function test_a_pickup_order_needs_a_pickup_branch(): void
    {
        $this->shopper();

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('pickupBranchId');
    }

    public function test_a_branch_without_enough_stock_cannot_be_chosen_and_nothing_is_taken(): void
    {
        [$customer, $origin, $other, $product] = $this->shopper(2);
        $thin = Branch::factory()->create(['supports_pickup' => true]);
        $this->stock($thin, $product, 1);

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupBranchId' => $thin->code, 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('pickupBranchId');

        $this->assertSame(0, Order::where('customer_id', $customer->id)->count());
        $this->assertSame(1, $product->stockAt($thin)->fresh()->quantity);
    }

    public function test_a_branch_that_is_closed_or_not_a_pickup_point_cannot_be_chosen(): void
    {
        [, , , $product] = $this->shopper();
        $noPickup = Branch::factory()->create(['supports_pickup' => false]);
        $closed = Branch::factory()->create(['supports_pickup' => true, 'status' => 'tutup sementara']);
        $this->stock($noPickup, $product, 10);
        $this->stock($closed, $product, 10);

        foreach ([$noPickup, $closed] as $branch) {
            $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupBranchId' => $branch->code, 'pickupEta' => 'Hari ini'])
                ->assertSessionHasErrors('pickupBranchId');
        }

        $this->post('/checkout', ['fulfilment' => 'ambil', 'paymentMethod' => 'online', 'pickupBranchId' => 'CB-TIDAK-ADA', 'pickupEta' => 'Hari ini'])
            ->assertSessionHasErrors('pickupBranchId');
    }

    public function test_delivery_needs_no_pickup_branch(): void
    {
        $this->shopper();

        $this->post('/checkout', ['fulfilment' => 'antar', 'paymentMethod' => 'online'])
            ->assertSessionDoesntHaveErrors('pickupBranchId');
    }

    public function test_the_checkout_page_lists_the_pickup_branches_with_those_that_can_fill_the_order_first(): void
    {
        [, $origin, $other, $product] = $this->shopper(3 - 1);
        $thin = Branch::factory()->create(['supports_pickup' => true, 'name' => 'Stok Tipis']);
        $this->stock($thin, $product, 1);
        Branch::factory()->create(['supports_pickup' => false, 'name' => 'Bukan Tempat Ambil']);

        $this->get('/checkout')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/Checkout')
            ->has('pickupBranches', 3)
            ->where('pickupBranches.0.selectable', true)
            ->where('pickupBranches.1.selectable', true)
            ->where('pickupBranches.2.name', 'Stok Tipis')
            ->where('pickupBranches.2.selectable', false)
            ->has('pickupBranches.0.fullAddress')
        );
    }
}

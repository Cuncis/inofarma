<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * A coupon is checked when it is applied to the cart, and again at checkout:
 * the cart, the coupon and the branch can all change in between.
 */
class CheckoutCouponSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    private Branch $branch;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);
        Http::fake([
            'api-sandbox.doku.com/*' => Http::response([
                'message' => ['SUCCESS'],
                'response' => [
                    'order' => ['amount' => '0', 'invoice_number' => 'x', 'session_id' => 'sess'],
                    'payment' => ['token_id' => 'tok', 'url' => 'https://sandbox.doku.com/checkout-link-v2/tok', 'expired_date' => '20260101000000'],
                ],
            ], 200),
        ]);

        $this->customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($this->customer, 'customer');

        $this->branch = Branch::factory()->create(['supports_pickup' => true]);
        $this->product = Product::factory()->create(['price' => 60000]);
        BranchStock::factory()->for($this->branch)->for($this->product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($this->branch)->for($this->product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);
    }

    private function cartWithCoupon(Coupon $coupon, int $quantity = 2): void
    {
        $this->post('/keranjang', ['productId' => $this->product->sku, 'branchId' => $this->branch->code, 'quantity' => $quantity])
            ->assertSessionHasNoErrors();
        $this->post('/keranjang/kupon', ['code' => $coupon->code])->assertSessionHasNoErrors();
    }

    private function checkout(?Branch $pickupBranch = null): TestResponse
    {
        return $this->post('/checkout', [
            'fulfilment' => 'ambil',
            'paymentMethod' => 'online',
            'pickupBranchId' => ($pickupBranch ?? $this->branch)->code,
        ]);
    }

    public function test_dropping_below_the_minimum_purchase_after_applying_refuses_the_coupon(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'nominal', 'value' => 50000, 'minimum_purchase' => 100000]);
        $this->cartWithCoupon($coupon);

        $this->patch("/keranjang/{$this->product->sku}", ['quantity' => 1]);

        $this->checkout()->assertSessionHasErrors('code');
        $this->assertSame(0, $this->customer->orders()->count());
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertNull($this->customer->cart()->first()->coupon_id);
    }

    public function test_a_coupon_deactivated_after_applying_is_refused_at_checkout(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'persentase', 'value' => 50, 'minimum_purchase' => 0]);
        $this->cartWithCoupon($coupon);

        $coupon->update(['status' => 'nonaktif']);

        $this->checkout()->assertSessionHasErrors('code');
        $this->assertSame(0, $this->customer->orders()->count());
    }

    public function test_an_exhausted_coupon_is_refused_at_checkout(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'persentase', 'value' => 50, 'minimum_purchase' => 0, 'quota' => 1]);
        $this->cartWithCoupon($coupon);

        $coupon->update(['used_count' => 1]);

        $this->checkout()->assertSessionHasErrors('code');
        $this->assertSame(0, $this->customer->orders()->count());
    }

    public function test_a_branch_scoped_coupon_is_refused_when_picking_up_at_another_branch(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'persentase', 'value' => 50, 'minimum_purchase' => 0]);
        $coupon->branches()->attach($this->branch);
        $this->cartWithCoupon($coupon);

        $other = Branch::factory()->create(['supports_pickup' => true]);

        $this->checkout($other)->assertSessionHasErrors('code');
        $this->assertSame(0, $this->customer->orders()->count());
    }

    public function test_a_still_valid_coupon_is_honoured_at_checkout(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'nominal', 'value' => 20000, 'minimum_purchase' => 100000]);
        $this->cartWithCoupon($coupon);

        $this->checkout()->assertSessionHasNoErrors();

        $order = $this->customer->orders()->sole();
        $this->assertSame(20000, $order->discount_total);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }
}

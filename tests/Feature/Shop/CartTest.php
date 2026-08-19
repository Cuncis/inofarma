<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CartTest extends TestCase
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

    public function test_a_guest_can_add_a_product_to_the_cart(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create(['name' => 'Paracetamol 500mg']);
        $this->stock($branch, $product, 10);

        $this->post('/ui/keranjang', [
            'productId' => $product->sku,
            'branchId' => $branch->code,
            'quantity' => 2,
        ])->assertSessionHasNoErrors();

        $this->get('/ui/cart')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/Cart')
                ->has('cart.items', 1)
                ->where('cart.items.0.name', 'Paracetamol 500mg')
                ->where('cart.items.0.quantity', 2)
                ->where('cart.branch.id', $branch->code)
            );
    }

    public function test_adding_a_product_from_a_different_branch_is_refused_by_default(): void
    {
        $branchA = Branch::factory()->create(['name' => 'Cabang Otista']);
        $branchB = Branch::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $this->stock($branchA, $productA, 10);
        $this->stock($branchB, $productB, 10);

        $this->post('/ui/keranjang', ['productId' => $productA->sku, 'branchId' => $branchA->code]);

        $this->post('/ui/keranjang', ['productId' => $productB->sku, 'branchId' => $branchB->code])
            ->assertSessionHasErrors('branch');

        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('cart.items', 1)
            ->where('cart.branch.id', $branchA->code)
        );
    }

    public function test_switching_branch_empties_the_cart_and_adds_from_the_new_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $this->stock($branchA, $productA, 10);
        $this->stock($branchB, $productB, 10);

        $this->post('/ui/keranjang', ['productId' => $productA->sku, 'branchId' => $branchA->code]);

        $this->post('/ui/keranjang', [
            'productId' => $productB->sku, 'branchId' => $branchB->code, 'switchBranch' => true,
        ])->assertSessionHasNoErrors();

        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('cart.items', 1)
            ->where('cart.items.0.sku', $productB->sku)
            ->where('cart.branch.id', $branchB->code)
        );
    }

    public function test_a_product_cannot_be_added_beyond_available_stock(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        $this->stock($branch, $product, 3);

        $this->post('/ui/keranjang', [
            'productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 5,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_a_product_cannot_be_added_beyond_its_purchase_limit(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create(['max_qty_per_order' => 2]);
        $this->stock($branch, $product, 50);

        $this->post('/ui/keranjang', [
            'productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 3,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_quantity_can_be_updated_and_dropping_to_zero_removes_the_line(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1]);

        $this->patch("/ui/keranjang/{$product->sku}", ['quantity' => 4]);
        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('cart.items.0.quantity', 4)
        );

        $this->patch("/ui/keranjang/{$product->sku}", ['quantity' => 0]);
        $this->get('/ui/cart')->assertRedirect(route('ui.cart-empty'));
    }

    public function test_an_item_can_be_removed_directly(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);

        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code]);
        $this->delete("/ui/keranjang/{$product->sku}")->assertSessionHasNoErrors();

        $this->get('/ui/cart')->assertRedirect(route('ui.cart-empty'));
    }

    /**
     * `CartController::index()` decides this before ever rendering the page
     * — a client-side redirect from an already-mounted `Shop/Cart` would
     * flash the (empty-looking) cart for one frame first.
     */
    public function test_visiting_the_cart_with_nothing_in_it_redirects_straight_to_cart_empty(): void
    {
        $this->get('/ui/cart')->assertRedirect(route('ui.cart-empty'));
    }

    public function test_a_guest_cart_merges_into_the_customers_cart_on_sign_in(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $customer = Customer::factory()->create(['password' => Hash::make('password'), 'status' => 'aktif']);

        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 2]);

        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'password']);

        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('cart.items', 1)
            ->where('cart.items.0.quantity', 2)
        );
    }

    public function test_applying_a_coupon_requires_signing_in(): void
    {
        $this->post('/ui/keranjang/kupon', ['code' => 'HEMAT'])
            ->assertRedirect(route('ui.signin'));
    }

    public function test_a_signed_in_customer_can_apply_a_valid_coupon(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        $this->stock($branch, $product, 10);
        $customer = Customer::factory()->create(['status' => 'aktif']);

        $this->actingAs($customer, 'customer');
        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1]);

        Coupon::factory()->create(['code' => 'HEMAT10', 'type' => 'persentase', 'value' => 10]);

        $this->post('/ui/keranjang/kupon', ['code' => 'hemat10'])->assertSessionHasNoErrors();

        $this->get('/ui/cart')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('cart.coupon.code', 'HEMAT10')
            ->where('cart.discount', 10000)
        );
    }

    public function test_a_coupon_below_the_minimum_purchase_is_rejected(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create(['price' => 10000]);
        $this->stock($branch, $product, 10);
        $customer = Customer::factory()->create(['status' => 'aktif']);

        $this->actingAs($customer, 'customer');
        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code, 'quantity' => 1]);

        Coupon::factory()->create(['code' => 'BESAR', 'minimum_purchase' => 500000]);

        $this->post('/ui/keranjang/kupon', ['code' => 'BESAR'])->assertSessionHasErrors('code');
    }

    public function test_a_coupon_scoped_to_another_branch_is_rejected(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $product = Product::factory()->create();
        $this->stock($branch, $product, 10);
        $customer = Customer::factory()->create(['status' => 'aktif']);

        $coupon = Coupon::factory()->create(['code' => 'CABANGLAIN']);
        $coupon->branches()->attach($otherBranch);

        $this->actingAs($customer, 'customer');
        $this->post('/ui/keranjang', ['productId' => $product->sku, 'branchId' => $branch->code]);

        $this->post('/ui/keranjang/kupon', ['code' => 'CABANGLAIN'])->assertSessionHasErrors('code');
    }
}

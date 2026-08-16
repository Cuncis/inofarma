<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Fase 3.2's "Selesai bila": a branch-scoped staff member cannot see another
 * branch's orders, stock, or customers — proven at the query layer via
 * `BranchScope`/`TransferBranchScope`/`CustomerBranchScope`, not just a
 * hidden UI element.
 */
class BranchScopeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $home;

    private Branch $other;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (PermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('Kasir', 'web')->syncPermissions([
            'Pesanan:Lihat', 'Pelanggan:Lihat', 'Inventaris:Lihat',
            'Inventaris:Sesuaikan Stok', 'Inventaris:Terima Barang',
        ]);
        Role::findOrCreate('Super Admin', 'web');

        $this->home = Branch::factory()->create(['name' => 'Cabang Rumah']);
        $this->other = Branch::factory()->create(['name' => 'Cabang Lain']);

        $this->kasir = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => true,
            'branch_id' => $this->home->id,
        ]);
        $this->kasir->assignRole('Kasir');
    }

    private function signInAsKasir(): void
    {
        $this->post('/admin/masuk', ['email' => $this->kasir->email, 'password' => 'password']);
    }

    public function test_a_branch_scoped_user_only_sees_their_own_branchs_orders(): void
    {
        $mine = Order::factory()->for($this->home)->create();
        Order::factory()->for($this->other)->create();

        $this->signInAsKasir();

        $this->get('/admin/pesanan')->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/OrderList')
            ->has('orders', 1)
            ->where('orders.0.id', $mine->number)
        );
    }

    public function test_a_branch_scoped_user_only_sees_their_own_branchs_stock(): void
    {
        $product = Product::factory()->create();
        BranchStock::factory()->for($this->home)->for($product)->create(['quantity' => 10]);
        BranchStock::factory()->for($this->other)->for($product)->create(['quantity' => 99]);

        $this->signInAsKasir();

        $this->assertSame(10, BranchStock::sum('quantity'));
    }

    public function test_a_branch_scoped_user_only_sees_customers_whove_ordered_at_their_branch(): void
    {
        $mine = Customer::factory()->create();
        Order::factory()->for($this->home)->for($mine)->create();

        $stranger = Customer::factory()->create();
        Order::factory()->for($this->other)->for($stranger)->create();

        $neverOrdered = Customer::factory()->create();

        $this->signInAsKasir();

        $visible = Customer::pluck('id')->all();

        $this->assertContains($mine->id, $visible);
        $this->assertNotContains($stranger->id, $visible);
        $this->assertNotContains($neverOrdered->id, $visible);
    }

    public function test_a_central_super_admin_sees_every_branch(): void
    {
        Order::factory()->for($this->home)->create();
        Order::factory()->for($this->other)->create();

        $superAdmin = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => true,
            'branch_id' => null,
        ]);
        $superAdmin->assignRole('Super Admin');

        $this->post('/admin/masuk', ['email' => $superAdmin->email, 'password' => 'password']);

        $this->get('/admin/pesanan')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('orders', 2)
        );
    }

    public function test_a_branch_scoped_user_cannot_adjust_another_branchs_stock(): void
    {
        $product = Product::factory()->create();
        BranchStock::factory()->for($this->other)->for($product)->create(['quantity' => 10]);

        $this->signInAsKasir();

        $this->post("/admin/inventaris/stok/{$this->other->code}/{$product->sku}/sesuaikan", [
            'delta' => 5,
            'reason' => 'Penyesuaian (stok opname)',
        ])->assertForbidden();
    }

    public function test_a_branch_scoped_user_cannot_view_another_branchs_stock_page(): void
    {
        $this->signInAsKasir();

        $this->get("/admin/inventaris/stok/{$this->other->code}")->assertForbidden();
    }
}

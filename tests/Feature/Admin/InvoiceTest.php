<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Faktur is a read-only view of `Order`, not its own table (see
 * `InvoicePresenter`) — these tests are about the status/due-date derivation
 * and the fact that it inherits `Order`'s branch scope for free, not about
 * CRUD, since there is none.
 */
class InvoiceTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_list_shows_one_invoice_per_order(): void
    {
        $this->get('/admin/faktur')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/InvoiceList')
                ->has('invoices', self::ORDER_COUNT)
            );
    }

    public function test_a_paid_order_reports_lunas(): void
    {
        $this->get('/admin/faktur/INO-2451')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/InvoiceDetail')
                ->where('invoice.status', 'Lunas')
            );
    }

    public function test_an_unpaid_order_reports_belum_bayar(): void
    {
        $this->get('/admin/faktur/INO-2445')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('invoice.status', 'Belum Bayar')
            );
    }

    public function test_the_due_date_is_the_orders_own_expiry(): void
    {
        $order = Order::where('number', 'INO-2445')->firstOrFail();
        $order->update(['expires_at' => now()->addDay()]);

        $this->get('/admin/faktur/INO-2445')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('invoice.due', $order->expires_at->translatedFormat('d M Y'))
            );
    }

    public function test_a_past_expiry_on_an_unpaid_order_reports_jatuh_tempo(): void
    {
        $order = Order::where('number', 'INO-2445')->firstOrFail();
        $order->update(['expires_at' => now()->subDay()]);

        $this->get('/admin/faktur/INO-2445')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('invoice.status', 'Jatuh Tempo')
            );
    }

    public function test_line_items_match_the_order(): void
    {
        $order = Order::with('items')->where('number', 'INO-2446')->firstOrFail();

        $this->get('/admin/faktur/INO-2446')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('invoice.items', $order->items->count())
                ->where('invoice.total', $order->grand_total)
            );
    }

    public function test_an_unknown_invoice_is_a_404(): void
    {
        $this->get('/admin/faktur/INO-9999')->assertNotFound();
    }

    public function test_branch_scoped_staff_only_see_their_own_orders_as_invoices(): void
    {
        foreach (PermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('Kasir Faktur', 'web')->syncPermissions(['Pesanan:Lihat']);

        $home = Branch::orderBy('id')->first();
        $kasir = User::factory()->create(['branch_id' => $home->id, 'password' => Hash::make('password')]);
        $kasir->assignRole('Kasir Faktur');

        $this->post('/admin/keluar');
        $this->post('/admin/masuk', ['email' => $kasir->email, 'password' => 'password']);

        $expected = Order::forBranch($home)->count();

        $this->get('/admin/faktur')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('invoices', $expected));

        $this->assertLessThan(self::ORDER_COUNT, $expected);
    }
}

<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `InvoiceTest`, exercised through the Filament resource
 * at /admin instead of the legacy Inertia routes.
 */
class InvoiceResourceTest extends TestCase
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
        Livewire::test(ListInvoices::class)
            ->assertCanSeeTableRecords(Order::all());

        $this->assertSame(self::ORDER_COUNT, Order::count());
    }

    public function test_a_paid_order_reports_lunas(): void
    {
        $order = Order::where('number', 'INO-2451')->firstOrFail();

        Livewire::test(ListInvoices::class)
            ->assertTableColumnFormattedStateSet('status', 'Lunas', record: $order);
    }

    public function test_an_unpaid_order_reports_belum_bayar(): void
    {
        $order = Order::where('number', 'INO-2445')->firstOrFail();

        Livewire::test(ListInvoices::class)
            ->assertTableColumnFormattedStateSet('status', 'Belum Bayar', record: $order);
    }

    public function test_a_past_expiry_on_an_unpaid_order_reports_jatuh_tempo(): void
    {
        $order = Order::where('number', 'INO-2445')->firstOrFail();
        $order->update(['expires_at' => now()->subDay()]);

        Livewire::test(ListInvoices::class)
            ->assertTableColumnFormattedStateSet('status', 'Jatuh Tempo', record: $order);
    }

    public function test_line_items_match_the_order(): void
    {
        $order = Order::with('items')->where('number', 'INO-2446')->firstOrFail();

        Livewire::test(ViewInvoice::class, ['record' => $order->getKey()])
            ->assertSee($order->items->first()->product_name)
            ->assertSee($order->number);
    }

    public function test_an_unknown_invoice_is_a_404(): void
    {
        $this->get('/admin/faktur/999999')->assertNotFound();
    }

    public function test_a_paid_order_can_be_refunded(): void
    {
        $order = Order::factory()->create([
            'customer_id' => Customer::factory(),
            'status' => 'diproses',
            'payment_status' => 'lunas',
        ]);
        Payment::factory()->for($order)->success()->create();

        Livewire::test(ViewInvoice::class, ['record' => $order->getKey()])
            ->assertActionVisible('refund')
            ->callAction('refund', data: ['note' => 'Dikembalikan lewat transfer bank.'])
            ->assertHasNoActionErrors();

        $order->refresh();
        $this->assertSame('refund', $order->payment_status);

        $payment = $order->latest_payment;
        $this->assertSame('refunded', $payment?->status);
        $this->assertSame('Dikembalikan lewat transfer bank.', $payment?->refund_note);
        $this->assertNotNull($payment?->refunded_at);
    }

    public function test_an_unpaid_order_cannot_be_refunded(): void
    {
        $order = Order::where('number', 'INO-2445')->firstOrFail();

        Livewire::test(ViewInvoice::class, ['record' => $order->getKey()])
            ->assertActionHidden('refund');
    }

    public function test_branch_scoped_staff_only_see_their_own_orders_as_invoices(): void
    {
        foreach (PermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('Kasir Faktur Beta', 'web')->syncPermissions(['Pesanan:Lihat']);

        $home = Branch::orderBy('id')->first();
        $kasir = User::factory()->create(['branch_id' => $home->id, 'password' => Hash::make('password')]);
        $kasir->assignRole('Kasir Faktur Beta');

        $this->post('/admin/keluar');
        $this->post('/admin/masuk', ['email' => $kasir->email, 'password' => 'password']);

        $expected = Order::forBranch($home)->get();

        Livewire::test(ListInvoices::class)
            ->assertCanSeeTableRecords($expected)
            ->assertCountTableRecords($expected->count());

        $this->assertLessThan(self::ORDER_COUNT, $expected->count());
    }
}

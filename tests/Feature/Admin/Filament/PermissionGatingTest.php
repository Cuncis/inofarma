<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Every legacy admin route group is gated by `permission:Module:Ability`
 * middleware (see routes/web.php) — this closes the gap flagged after the
 * Staf/Peran module: none of the Filament resources enforced any of that
 * on their own. One representative check per gated resource, matching the
 * exact permission the legacy route required.
 */
class PermissionGatingTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function signInAs(string $roleName, array $permissions): User
    {
        $this->post('/admin/keluar');

        Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $user->assignRole($roleName);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);

        return $user;
    }

    public function test_cabang_requires_cabang_lihat(): void
    {
        $this->signInAs('Tanpa Cabang', []);
        $this->get('/admin/beta/cabang')->assertForbidden();

        $this->signInAs('Dengan Cabang', ['Cabang:Lihat']);
        $this->get('/admin/beta/cabang')->assertOk();
    }

    public function test_pelanggan_requires_pelanggan_lihat(): void
    {
        $this->signInAs('Tanpa Pelanggan', []);
        $this->get('/admin/beta/pelanggan')->assertForbidden();

        $this->signInAs('Dengan Pelanggan', ['Pelanggan:Lihat']);
        $this->get('/admin/beta/pelanggan')->assertOk();
    }

    public function test_pesanan_requires_pesanan_lihat(): void
    {
        $this->signInAs('Tanpa Pesanan', []);
        $this->get('/admin/beta/pesanan')->assertForbidden();

        $this->signInAs('Dengan Pesanan', ['Pesanan:Lihat']);
        $this->get('/admin/beta/pesanan')->assertOk();
    }

    public function test_faktur_requires_pesanan_lihat_and_refund_requires_pesanan_refund(): void
    {
        $order = Order::factory()->create(['payment_status' => 'lunas']);

        $this->signInAs('Tanpa Faktur', []);
        $this->get('/admin/beta/faktur')->assertForbidden();

        $this->signInAs('Faktur Tanpa Refund', ['Pesanan:Lihat']);
        Livewire::test(ViewInvoice::class, ['record' => $order->getKey()])
            ->assertActionHidden('refund');

        $this->signInAs('Faktur Dengan Refund', ['Pesanan:Lihat', 'Pesanan:Refund']);
        Livewire::test(ViewInvoice::class, ['record' => $order->getKey()])
            ->assertActionVisible('refund');
    }

    public function test_rekonsiliasi_requires_pesanan_lihat(): void
    {
        $this->signInAs('Tanpa Rekonsiliasi', []);
        $this->get('/admin/beta/rekonsiliasi')->assertForbidden();

        $this->signInAs('Dengan Rekonsiliasi', ['Pesanan:Lihat']);
        $this->get('/admin/beta/rekonsiliasi')->assertOk();
    }

    public function test_pengambilan_requires_pesanan_proses_not_just_lihat(): void
    {
        $this->signInAs('Hanya Lihat', ['Pesanan:Lihat']);
        $this->get('/admin/beta/pengambilan')->assertForbidden();

        $this->signInAs('Dengan Proses', ['Pesanan:Proses']);
        $this->get('/admin/beta/pengambilan')->assertOk();
    }

    public function test_inventaris_screens_require_inventaris_lihat(): void
    {
        $this->signInAs('Tanpa Inventaris', []);
        $this->get('/admin/beta/inventaris/stok')->assertForbidden();
        $this->get('/admin/beta/inventaris/matriks')->assertForbidden();
        $this->get('/admin/beta/inventaris/transfer')->assertForbidden();

        $this->signInAs('Dengan Inventaris', ['Inventaris:Lihat']);
        $this->get('/admin/beta/inventaris/stok')->assertOk();
        $this->get('/admin/beta/inventaris/matriks')->assertOk();
        $this->get('/admin/beta/inventaris/transfer')->assertOk();
    }

    public function test_staf_requires_pengaturan_ubah(): void
    {
        $this->signInAs('Tanpa Staf', []);
        $this->get('/admin/beta/staf')->assertForbidden();

        $this->signInAs('Dengan Staf', ['Pengaturan:Ubah']);
        $this->get('/admin/beta/staf')->assertOk();
    }

    public function test_peran_requires_peran_lihat_and_write_actions_require_peran_ubah(): void
    {
        $this->signInAs('Tanpa Peran', []);
        $this->get('/admin/beta/peran')->assertForbidden();

        $this->signInAs('Peran Lihat Saja', ['Peran:Lihat']);
        $this->get('/admin/beta/peran')->assertOk();
        $this->get('/admin/beta/peran/create')->assertForbidden();

        $this->signInAs('Peran Ubah', ['Peran:Lihat', 'Peran:Ubah']);
        $this->get('/admin/beta/peran/create')->assertOk();
    }

    public function test_catalogue_screens_have_no_permission_gate_matching_legacy(): void
    {
        $this->signInAs('Staf Biasa', []);

        $this->get('/admin/beta/atribut')->assertOk();
        $this->get('/admin/beta/pemasok')->assertOk();
        $this->get('/admin/beta/kupon')->assertOk();
    }
}

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
        $this->get('/admin/cabang')->assertForbidden();

        $this->signInAs('Dengan Cabang', ['Cabang:Lihat']);
        $this->get('/admin/cabang')->assertOk();
    }

    public function test_pelanggan_requires_pelanggan_lihat(): void
    {
        $this->signInAs('Tanpa Pelanggan', []);
        $this->get('/admin/pelanggan')->assertForbidden();

        $this->signInAs('Dengan Pelanggan', ['Pelanggan:Lihat']);
        $this->get('/admin/pelanggan')->assertOk();
    }

    public function test_pesanan_requires_pesanan_lihat(): void
    {
        $this->signInAs('Tanpa Pesanan', []);
        $this->get('/admin/pesanan')->assertForbidden();

        $this->signInAs('Dengan Pesanan', ['Pesanan:Lihat']);
        $this->get('/admin/pesanan')->assertOk();
    }

    public function test_faktur_requires_pesanan_lihat_and_refund_requires_pesanan_refund(): void
    {
        $order = Order::factory()->create(['payment_status' => 'lunas']);

        $this->signInAs('Tanpa Faktur', []);
        $this->get('/admin/faktur')->assertForbidden();

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
        $this->get('/admin/rekonsiliasi')->assertForbidden();

        $this->signInAs('Dengan Rekonsiliasi', ['Pesanan:Lihat']);
        $this->get('/admin/rekonsiliasi')->assertOk();
    }

    public function test_pengambilan_requires_pesanan_proses_not_just_lihat(): void
    {
        $this->signInAs('Hanya Lihat', ['Pesanan:Lihat']);
        $this->get('/admin/pengambilan')->assertForbidden();

        $this->signInAs('Dengan Proses', ['Pesanan:Proses']);
        $this->get('/admin/pengambilan')->assertOk();
    }

    public function test_inventaris_screens_require_inventaris_lihat(): void
    {
        $this->signInAs('Tanpa Inventaris', []);
        $this->get('/admin/inventaris/stok')->assertForbidden();
        $this->get('/admin/inventaris/matriks')->assertForbidden();
        $this->get('/admin/inventaris/transfer')->assertForbidden();

        $this->signInAs('Dengan Inventaris', ['Inventaris:Lihat']);
        $this->get('/admin/inventaris/stok')->assertOk();
        $this->get('/admin/inventaris/matriks')->assertOk();
        $this->get('/admin/inventaris/transfer')->assertOk();
    }

    public function test_staf_requires_pengaturan_ubah(): void
    {
        $this->signInAs('Tanpa Staf', []);
        $this->get('/admin/staf')->assertForbidden();

        $this->signInAs('Dengan Staf', ['Pengaturan:Ubah']);
        $this->get('/admin/staf')->assertOk();
    }

    public function test_peran_requires_peran_lihat_and_write_actions_require_peran_ubah(): void
    {
        $this->signInAs('Tanpa Peran', []);
        $this->get('/admin/peran')->assertForbidden();

        $this->signInAs('Peran Lihat Saja', ['Peran:Lihat']);
        $this->get('/admin/peran')->assertOk();
        $this->get('/admin/peran/create')->assertForbidden();

        $this->signInAs('Peran Ubah', ['Peran:Lihat', 'Peran:Ubah']);
        $this->get('/admin/peran/create')->assertOk();
    }

    public function test_catalogue_screens_have_no_permission_gate_matching_legacy(): void
    {
        $this->signInAs('Staf Biasa', []);

        $this->get('/admin/atribut')->assertOk();
        $this->get('/admin/pemasok')->assertOk();
        $this->get('/admin/kupon')->assertOk();
        $this->get('/admin/kategori')->assertOk();
    }

    public function test_produk_requires_produk_lihat(): void
    {
        $this->signInAs('Tanpa Produk', []);
        $this->get('/admin/produk')->assertForbidden();

        $this->signInAs('Dengan Produk', ['Produk:Lihat']);
        $this->get('/admin/produk')->assertOk();
    }
}

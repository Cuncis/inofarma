<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Orders\OrderResource;
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

    /**
     * These used to be open to any staff login, which let a Kasir create a
     * 100% coupon. Product master data follows Produk; coupons are Super Admin only.
     */
    public function test_catalogue_screens_require_produk_and_coupons_are_super_admin_only(): void
    {
        $this->signInAs('Staf Biasa', []);

        $this->get('/admin/atribut')->assertForbidden();
        $this->get('/admin/pemasok')->assertForbidden();
        $this->get('/admin/kupon')->assertForbidden();
        $this->get('/admin/kategori')->assertForbidden();

        $this->signInAs('Produk Lihat Saja', ['Produk:Lihat']);

        $this->get('/admin/atribut')->assertOk();
        $this->get('/admin/pemasok')->assertOk();
        $this->get('/admin/kategori')->assertOk();
        $this->get('/admin/kategori/create')->assertForbidden();
        $this->get('/admin/kupon')->assertForbidden();

        $this->signInAs('Pengaturan Penuh', ['Pengaturan:Lihat', 'Pengaturan:Ubah']);

        $this->get('/admin/kupon')->assertForbidden();

        $this->post('/admin/keluar');
        $this->signInAsAdmin();

        $this->get('/admin/kupon')->assertOk();
        $this->get('/admin/kupon/create')->assertOk();
    }

    public function test_only_super_admin_may_delete_an_order(): void
    {
        $order = Order::factory()->create();

        Role::findOrCreate('Semua Pesanan', 'web')->syncPermissions(['Pesanan:Lihat', 'Pesanan:Proses', 'Pesanan:Batalkan', 'Pengaturan:Ubah']);
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole('Semua Pesanan');

        $this->actingAs($manager, 'web');
        $this->assertTrue(OrderResource::canEdit($order));
        $this->assertFalse(OrderResource::canDelete($order));
        $this->assertFalse(OrderResource::canForceDelete($order));

        $this->actingAs(User::where('email', 'admin@inofarma.co.id')->first(), 'web');
        $this->assertTrue(OrderResource::canDelete($order));
    }

    /**
     * Opening a screen is not permission to change it: every resource maps
     * create/edit/delete to its own Tambah/Ubah/Hapus (or Proses) ability.
     */
    public function test_view_permission_alone_cannot_create_or_edit(): void
    {
        $order = Order::factory()->create();

        $this->signInAs('Lihat Saja', ['Produk:Lihat', 'Pelanggan:Lihat', 'Pesanan:Lihat', 'Cabang:Lihat', 'Inventaris:Lihat']);

        $this->get('/admin/produk/create')->assertForbidden();
        $this->get('/admin/pelanggan/create')->assertForbidden();
        $this->get('/admin/cabang/create')->assertForbidden();
        $this->get('/admin/pesanan/create')->assertForbidden();
        $this->get("/admin/pesanan/{$order->id}/edit")->assertForbidden();
        $this->get('/admin/inventaris/transfer/create')->assertForbidden();

        $this->signInAs('Produk Tambah', ['Produk:Lihat', 'Produk:Tambah']);
        $this->get('/admin/produk/create')->assertOk();

        $this->signInAs('Pesanan Proses', ['Pesanan:Lihat', 'Pesanan:Proses']);
        $this->get("/admin/pesanan/{$order->id}/edit")->assertOk();
    }

    public function test_newsletter_and_subscriber_screens_require_konten(): void
    {
        $this->signInAs('Staf Biasa', []);

        $this->get('/admin/newsletter')->assertForbidden();
        $this->get('/admin/subscriber')->assertForbidden();

        $this->signInAs('Konten Lihat', ['Konten:Lihat']);

        $this->get('/admin/newsletter')->assertOk();
        $this->get('/admin/subscriber')->assertOk();
        $this->get('/admin/newsletter/create')->assertForbidden();
    }

    public function test_produk_requires_produk_lihat(): void
    {
        $this->signInAs('Tanpa Produk', []);
        $this->get('/admin/produk')->assertForbidden();

        $this->signInAs('Dengan Produk', ['Produk:Lihat']);
        $this->get('/admin/produk')->assertOk();
    }
}

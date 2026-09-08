<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\PermissionMatrix;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `RoleController::matrix()`/`updateMatrix()`, exercised
 * through the Filament page at /admin/hak-akses.
 */
class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_matrix_lists_every_role_and_permission_module(): void
    {
        Livewire::test(PermissionMatrix::class)
            ->assertSee('Super Admin')
            ->assertSee('Kasir')
            ->assertSee('Produk')
            ->assertSee('Inventaris')
            ->assertSee('Lihat');
    }

    public function test_saving_the_matrix_grants_and_revokes_permissions(): void
    {
        $kasir = Role::where('name', 'Kasir')->firstOrFail();
        $this->assertFalse($kasir->hasPermissionTo('Produk:Lihat'));
        $this->assertTrue($kasir->hasPermissionTo('Pesanan:Lihat'));

        Livewire::test(PermissionMatrix::class)
            ->set('grants.Kasir.Produk:Lihat', true)
            ->set('grants.Kasir.Pesanan:Lihat', false)
            ->call('save');

        $kasir->refresh();
        $this->assertTrue($kasir->hasPermissionTo('Produk:Lihat'));
        $this->assertFalse($kasir->hasPermissionTo('Pesanan:Lihat'));
    }

    public function test_a_staff_member_without_view_permission_is_forbidden(): void
    {
        $this->post('/admin/keluar');

        $limited = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $limited->assignRole('Kasir');

        $this->post('/admin/masuk', ['email' => $limited->email, 'password' => 'password']);

        $this->get('/admin/hak-akses')->assertForbidden();
    }

    public function test_saving_without_the_update_permission_is_refused(): void
    {
        $this->post('/admin/keluar');

        Role::findOrCreate('Pengawas Hak Akses', 'web')->syncPermissions(['Peran:Lihat']);
        $viewer = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $viewer->assignRole('Pengawas Hak Akses');

        $this->post('/admin/masuk', ['email' => $viewer->email, 'password' => 'password']);

        $kasir = Role::where('name', 'Kasir')->firstOrFail();

        Livewire::test(PermissionMatrix::class)
            ->set('grants.Kasir.Produk:Lihat', true)
            ->call('save');

        $this->assertFalse($kasir->fresh()->hasPermissionTo('Produk:Lihat'));
    }
}

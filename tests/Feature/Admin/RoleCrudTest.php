<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class RoleCrudTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_five_seeded_roles_are_listed(): void
    {
        $this->get('/admin/peran')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/RoleList')
                ->has('roles', 5)
            );
    }

    public function test_a_role_can_be_created_with_permissions(): void
    {
        $this->post('/admin/peran', [
            'name' => 'Peran Baru',
            'description' => 'Deskripsi peran baru.',
            'permissions' => ['Produk:Lihat', 'Pesanan:Lihat'],
        ])
            ->assertRedirect(route('admin.peran.index'))
            ->assertSessionHas('success');

        $role = Role::where('name', 'Peran Baru')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('Produk:Lihat'));
        $this->assertTrue($role->hasPermissionTo('Pesanan:Lihat'));
        $this->assertFalse($role->hasPermissionTo('Pelanggan:Lihat'));
    }

    public function test_a_role_can_be_updated(): void
    {
        $this->post('/admin/peran', [
            'name' => 'Sementara',
            'permissions' => ['Produk:Lihat'],
        ]);

        $this->put('/admin/peran/Sementara', [
            'name' => 'Sementara',
            'description' => 'Sudah diubah.',
            'permissions' => ['Pelanggan:Lihat'],
        ])->assertRedirect(route('admin.peran.index'));

        $role = Role::where('name', 'Sementara')->first();
        $this->assertFalse($role->hasPermissionTo('Produk:Lihat'));
        $this->assertTrue($role->hasPermissionTo('Pelanggan:Lihat'));
    }

    public function test_a_role_still_assigned_to_staff_cannot_be_deleted(): void
    {
        $this->delete('/admin/peran/'.rawurlencode('Super Admin'))
            ->assertRedirect(route('admin.peran.index'))
            ->assertSessionHas('error');

        $this->assertNotNull(Role::where('name', 'Super Admin')->first());
    }

    public function test_an_unused_role_can_be_deleted(): void
    {
        $this->post('/admin/peran', ['name' => 'Sekali Pakai', 'permissions' => []]);

        $this->delete('/admin/peran/Sekali Pakai')
            ->assertRedirect(route('admin.peran.index'))
            ->assertSessionHas('success');

        $this->assertNull(Role::where('name', 'Sekali Pakai')->first());
    }

    public function test_a_staff_member_without_permission_is_forbidden(): void
    {
        $this->post('/admin/keluar');

        $limited = User::factory()->create([
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $limited->assignRole('Kasir');

        $this->post('/admin/masuk', ['email' => $limited->email, 'password' => 'password']);

        $this->get('/admin/peran')->assertForbidden();
    }
}

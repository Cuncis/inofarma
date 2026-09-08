<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `RoleCrudTest`, exercised through the Filament resource
 * at /admin/beta instead of the legacy Inertia routes.
 */
class RoleResourceTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_five_seeded_roles_are_listed(): void
    {
        Livewire::test(ListRoles::class)
            ->assertCanSeeTableRecords(Role::all());

        $this->assertSame(5, Role::count());
    }

    public function test_a_role_can_be_created_with_permissions(): void
    {
        $permissions = Permission::whereIn('name', ['Produk:Lihat', 'Pesanan:Lihat'])->pluck('id');

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Peran Baru',
                'description' => 'Deskripsi peran baru.',
                'permissions' => $permissions->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'Peran Baru')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('Produk:Lihat'));
        $this->assertTrue($role->hasPermissionTo('Pesanan:Lihat'));
        $this->assertFalse($role->hasPermissionTo('Pelanggan:Lihat'));
    }

    public function test_a_role_can_be_updated(): void
    {
        $produkLihat = Permission::where('name', 'Produk:Lihat')->firstOrFail();
        $pelangganLihat = Permission::where('name', 'Pelanggan:Lihat')->firstOrFail();

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Sementara', 'permissions' => [$produkLihat->id]])
            ->call('create');

        $role = Role::where('name', 'Sementara')->firstOrFail();

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->fillForm(['description' => 'Sudah diubah.', 'permissions' => [$pelangganLihat->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('Produk:Lihat'));
        $this->assertTrue($role->hasPermissionTo('Pelanggan:Lihat'));
    }

    public function test_a_role_still_assigned_to_staff_cannot_be_deleted(): void
    {
        $role = Role::where('name', 'Super Admin')->firstOrFail();

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete');

        $this->assertNotNull(Role::where('name', 'Super Admin')->first());
    }

    public function test_an_unused_role_can_be_deleted(): void
    {
        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Sekali Pakai'])
            ->call('create');

        $role = Role::where('name', 'Sekali Pakai')->firstOrFail();

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete');

        $this->assertNull(Role::where('name', 'Sekali Pakai')->first());
    }

    // NOTE: legacy asserts a permission-less staff member is forbidden here
    // (`RoleCrudTest::test_a_staff_member_without_permission_is_forbidden`).
    // The Filament panel does not yet enforce PermissionCatalog permissions
    // on any resource — that's cross-cutting across every module built so
    // far, not specific to Peran, and is flagged for the Hak Akses phase
    // rather than retrofitted here piecemeal.
}

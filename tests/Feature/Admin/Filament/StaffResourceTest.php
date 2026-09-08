<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `StaffCrudTest`, exercised through the Filament resource
 * at /admin instead of the legacy Inertia routes.
 */
class StaffResourceTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_seeded_staff_are_listed(): void
    {
        Livewire::test(ListStaff::class)
            ->assertCanSeeTableRecords(User::all());

        $this->assertSame(3, User::count());
    }

    public function test_a_staff_account_can_be_created_with_a_branch_and_role(): void
    {
        $branch = Branch::first();
        $kasir = Role::where('name', 'Kasir')->firstOrFail();

        Livewire::test(CreateStaff::class)
            ->fillForm([
                'name' => 'Staf Baru',
                'email' => 'staf.baru@inofarma.co.id',
                'password' => 'kata-sandi-baru',
                'password_confirmation' => 'kata-sandi-baru',
                'branch_id' => $branch->id,
                'is_active' => true,
                'roles' => [$kasir->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $staff = User::where('email', 'staf.baru@inofarma.co.id')->firstOrFail();
        $this->assertSame($branch->id, $staff->branch_id);
        $this->assertTrue($staff->hasRole('Kasir'));
    }

    public function test_a_staff_account_can_be_deactivated(): void
    {
        $staff = User::where('email', 'kasir.cb001@inofarma.co.id')->firstOrFail();

        Livewire::test(EditStaff::class, ['record' => $staff->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($staff->fresh()->is_active);
    }

    public function test_a_deactivated_staff_member_cannot_sign_in(): void
    {
        $staff = User::where('email', 'kasir.cb001@inofarma.co.id')->firstOrFail();
        $staff->update(['is_active' => false]);

        $this->post('/admin/keluar');
        $this->post('/admin/masuk', ['email' => $staff->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_leaving_the_password_blank_keeps_the_existing_one(): void
    {
        $staff = User::where('email', 'kasir.cb001@inofarma.co.id')->firstOrFail();
        $before = $staff->password;

        Livewire::test(EditStaff::class, ['record' => $staff->getKey()])
            ->fillForm(['name' => $staff->name])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($before, $staff->fresh()->password);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $me = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(EditStaff::class, ['record' => $me->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($me);
    }
}

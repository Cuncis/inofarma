<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class StaffCrudTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_seeded_staff_are_listed(): void
    {
        $this->get('/admin/staf')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/StaffList')
                ->has('staff', 3)
            );
    }

    public function test_a_staff_account_can_be_created_with_a_branch_and_role(): void
    {
        $branch = Branch::first();

        $this->post('/admin/staf', [
            'name' => 'Staf Baru',
            'email' => 'staf.baru@inofarma.co.id',
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
            'branchId' => $branch->id,
            'isActive' => true,
            'roles' => ['Kasir'],
        ])
            ->assertRedirect(route('admin.staf.index'))
            ->assertSessionHas('success');

        $staff = User::where('email', 'staf.baru@inofarma.co.id')->first();
        $this->assertNotNull($staff);
        $this->assertSame($branch->id, $staff->branch_id);
        $this->assertTrue($staff->hasRole('Kasir'));
    }

    public function test_a_staff_account_can_be_deactivated(): void
    {
        $staff = User::where('email', 'kasir.cb001@inofarma.co.id')->first();

        $this->put("/admin/staf/{$staff->id}", [
            'name' => $staff->name,
            'email' => $staff->email,
            'branchId' => $staff->branch_id,
            'isActive' => false,
            'roles' => ['Kasir'],
        ])->assertRedirect(route('admin.staf.index'));

        $this->assertFalse($staff->fresh()->is_active);
    }

    public function test_a_deactivated_staff_member_cannot_sign_in(): void
    {
        $staff = User::where('email', 'kasir.cb001@inofarma.co.id')->first();
        $staff->update(['is_active' => false]);

        $this->post('/admin/keluar');
        $this->post('/admin/masuk', ['email' => $staff->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $me = User::where('email', 'admin@inofarma.co.id')->first();

        $this->delete("/admin/staf/{$me->id}")
            ->assertRedirect(route('admin.staf.index'))
            ->assertSessionHas('error');

        $this->assertNotNull($me->fresh());
    }
}

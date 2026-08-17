<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'HEMAT15',
            'type' => 'Persentase',
            'value' => 15,
            'minimumPurchase' => 100000,
            'quota' => 500,
            'startsAt' => '2026-01-01',
            'expiresAt' => '2026-12-31',
            'status' => 'Aktif',
            'branches' => [],
        ], $overrides);
    }

    public function test_a_coupon_can_be_created_with_no_branch_restriction(): void
    {
        $this->post('/admin/kupon', $this->validPayload())
            ->assertRedirect(route('admin.kupon.index'))
            ->assertSessionHas('success');

        $this->get('/admin/kupon')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/CouponList')
                ->has('coupons', 1)
                ->where('coupons.0.code', 'HEMAT15')
                ->where('coupons.0.appliesToAllBranches', true)
            );
    }

    public function test_a_coupon_can_be_scoped_to_specific_branches(): void
    {
        $branches = Branch::orderBy('id')->limit(2)->pluck('code');

        $this->post('/admin/kupon', $this->validPayload(['branches' => $branches->all()]))
            ->assertSessionHasNoErrors();

        $this->get('/admin/kupon')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('coupons.0.appliesToAllBranches', false)
                ->has('coupons.0.branchNames', 2)
            );
    }

    public function test_a_free_shipping_coupon_needs_no_value(): void
    {
        $this->post('/admin/kupon', $this->validPayload(['type' => 'Gratis Ongkir', 'value' => null]))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Coupon::where('code', 'HEMAT15')->value('value'));
    }

    public function test_a_percentage_coupon_requires_a_value(): void
    {
        $this->post('/admin/kupon', $this->validPayload(['value' => null]))
            ->assertSessionHasErrors('value');
    }

    public function test_the_code_must_be_unique(): void
    {
        $this->post('/admin/kupon', $this->validPayload());

        $this->post('/admin/kupon', $this->validPayload())
            ->assertSessionHasErrors(['code' => 'Kode kupon ini sudah dipakai.']);
    }

    public function test_expiry_cannot_precede_the_start_date(): void
    {
        $this->post('/admin/kupon', $this->validPayload([
            'startsAt' => '2026-06-01',
            'expiresAt' => '2026-01-01',
        ]))->assertSessionHasErrors('expiresAt');
    }

    public function test_an_exhausted_coupon_reports_habis(): void
    {
        Coupon::factory()->habis()->create(['code' => 'PENUH50', 'quota' => 10, 'used_count' => 10]);

        $this->get('/admin/kupon')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('coupons.0.status', 'Habis')
            );
    }

    public function test_a_coupon_can_be_updated(): void
    {
        $this->post('/admin/kupon', $this->validPayload());
        $coupon = Coupon::where('code', 'HEMAT15')->firstOrFail();

        $this->put("/admin/kupon/{$coupon->code}", $this->validPayload(['value' => 25]))
            ->assertRedirect(route('admin.kupon.index'))
            ->assertSessionHas('success');

        $this->assertSame(25, $coupon->refresh()->value);
    }

    public function test_a_coupon_can_be_deleted(): void
    {
        $this->post('/admin/kupon', $this->validPayload());

        $this->delete('/admin/kupon/HEMAT15')
            ->assertRedirect(route('admin.kupon.index'))
            ->assertSessionHas('success');

        $this->get('/admin/kupon')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('coupons', 0));
    }
}

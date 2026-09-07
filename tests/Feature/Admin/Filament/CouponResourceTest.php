<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Branch;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `CouponCrudTest`, exercised through the Filament resource
 * at /admin/beta instead of the legacy Inertia routes.
 */
class CouponResourceTest extends TestCase
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
            'type' => 'persentase',
            'value' => 15,
            'minimum_purchase' => 100000,
            'quota' => 500,
            'starts_at' => '2026-01-01',
            'expires_at' => '2026-12-31',
            'status' => 'aktif',
            'branches' => [],
        ], $overrides);
    }

    public function test_a_coupon_can_be_created_with_no_branch_restriction(): void
    {
        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $coupon = Coupon::where('code', 'HEMAT15')->firstOrFail();
        $this->assertTrue($coupon->applies_to_all_branches);
    }

    public function test_a_coupon_can_be_scoped_to_specific_branches(): void
    {
        $branchIds = Branch::orderBy('id')->limit(2)->pluck('id')->all();

        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload(['branches' => $branchIds]))
            ->call('create')
            ->assertHasNoFormErrors();

        $coupon = Coupon::where('code', 'HEMAT15')->firstOrFail();
        $this->assertFalse($coupon->applies_to_all_branches);
        $this->assertCount(2, $coupon->branches);
    }

    public function test_a_free_shipping_coupon_needs_no_value(): void
    {
        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload(['type' => 'ongkir gratis', 'value' => null]))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(0, Coupon::where('code', 'HEMAT15')->value('value'));
    }

    public function test_a_percentage_coupon_requires_a_value(): void
    {
        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload(['value' => null]))
            ->call('create')
            ->assertHasFormErrors(['value' => 'required']);
    }

    public function test_the_code_must_be_unique(): void
    {
        Coupon::factory()->create(['code' => 'HEMAT15']);

        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_expiry_cannot_precede_the_start_date(): void
    {
        Livewire::test(CreateCoupon::class)
            ->fillForm($this->validPayload([
                'starts_at' => '2026-06-01',
                'expires_at' => '2026-01-01',
            ]))
            ->call('create')
            ->assertHasFormErrors(['expires_at' => 'after_or_equal']);
    }

    public function test_an_exhausted_coupon_reports_habis(): void
    {
        Coupon::factory()->habis()->create(['code' => 'PENUH50', 'quota' => 10, 'used_count' => 10]);

        Livewire::test(ListCoupons::class)
            ->assertTableColumnFormattedStateSet('status', 'Habis', record: Coupon::where('code', 'PENUH50')->first());
    }

    public function test_a_coupon_can_be_updated(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'HEMAT15', 'value' => 15]);

        Livewire::test(EditCoupon::class, ['record' => $coupon->getKey()])
            ->fillForm($this->validPayload(['value' => 25]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(25, $coupon->refresh()->value);
    }

    public function test_a_coupon_can_be_deleted(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'HEMAT15']);

        Livewire::test(EditCoupon::class, ['record' => $coupon->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($coupon);
    }
}

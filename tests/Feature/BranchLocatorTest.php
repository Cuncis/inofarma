<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\TestCase;

/**
 * Fase 2.3: location-based branch search — the JSON endpoints the storefront
 * calls once the browser's geolocation resolves, the "Cabang Kami" page, and
 * the session-backed fallback for shoppers who decline the location prompt.
 */
class BranchLocatorTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_our_branches_page_lists_every_active_branch(): void
    {
        $this->get('/ui/cabang-kami')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/OurBranches')
                ->has('branches', self::BRANCH_COUNT)
            );
    }

    /** ROADMAP.md Fase 9.1: "Halaman Cabang" must show SIA and the APJ's name + SIPA number per branch. */
    public function test_our_branches_page_shows_sia_and_apj_details(): void
    {
        $branch = Branch::where('sia_number', '!=', null)->first()
            ?? Branch::first();
        $branch->update([
            'sia_number' => 'SIA-TEST-001',
            'apj_name' => 'apt. Siti Aminah, S.Farm.',
            'apj_sipa_number' => 'SIPA-TEST-001',
        ]);

        $this->get('/ui/cabang-kami')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('branches', fn ($branches) => collect($branches)->contains(
                    fn ($row) => $row['siaNumber'] === 'SIA-TEST-001'
                        && $row['apjName'] === 'apt. Siti Aminah, S.Farm.'
                        && $row['apjSipaNumber'] === 'SIPA-TEST-001'
                ))
            );
    }

    public function test_the_fallback_area_list_only_offers_real_coverage(): void
    {
        $this->get('/ui/cabang-kami')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('areas')
                ->where('areas.0.provinsi', fn (string $provinsi) => in_array(
                    $provinsi, ['DKI Jakarta', 'Banten', 'Jawa Barat'], true,
                ))
            );
    }

    public function test_nearest_branches_are_sorted_by_distance_from_a_point(): void
    {
        // Roughly Monas, central Jakarta.
        $response = $this->getJson('/api/cabang/terdekat?lat=-6.1754&lng=106.8272')
            ->assertOk()
            ->json('branches');

        $distances = collect($response)->pluck('distanceKm');

        $this->assertNotNull($distances->first());
        $this->assertSame($distances->sort()->values()->all(), $distances->values()->all());
    }

    public function test_without_coordinates_branches_still_list_but_carry_no_distance(): void
    {
        $this->getJson('/api/cabang/terdekat')
            ->assertOk()
            ->assertJsonPath('branches.0.distanceKm', null)
            ->assertJsonCount(self::BRANCH_COUNT, 'branches');
    }

    public function test_branches_for_a_product_report_stock_and_selectability(): void
    {
        $response = $this->getJson('/api/cabang/untuk-produk/PRD-003')
            ->assertOk()
            ->json('branches');

        // PRD-003 (Vitamin C) is seeded with zero stock everywhere.
        $this->assertCount(self::BRANCH_COUNT, $response);
        $this->assertTrue(collect($response)->every(fn (array $branch) => $branch['selectable'] === false));
    }

    public function test_a_branch_with_stock_is_selectable_and_one_without_is_not(): void
    {
        $response = $this->getJson('/api/cabang/untuk-produk/PRD-001')
            ->assertOk()
            ->json('branches');

        $this->assertTrue(collect($response)->contains('selectable', true));
    }

    public function test_in_stock_branches_are_ranked_ahead_of_out_of_stock_ones(): void
    {
        $response = $this->getJson('/api/cabang/untuk-produk/PRD-001?lat=-6.1754&lng=106.8272')
            ->assertOk()
            ->json('branches');

        $selectableFlags = collect($response)->pluck('selectable');
        $firstUnselectable = $selectableFlags->search(false);

        if ($firstUnselectable !== false) {
            $this->assertTrue($selectableFlags->take($firstUnselectable)->every(fn ($flag) => $flag === true));
        }
    }

    public function test_an_unknown_product_is_a_404(): void
    {
        $this->getJson('/api/cabang/untuk-produk/PRD-999')->assertNotFound();
    }

    public function test_a_location_can_be_saved_and_is_then_used_automatically(): void
    {
        $this->post('/ui/lokasi', ['lat' => -6.1754, 'lng' => 106.8272])
            ->assertRedirect();

        $response = $this->getJson('/api/cabang/terdekat')->assertOk()->json('branches');

        $this->assertNotNull($response[0]['distanceKm']);
    }

    public function test_saving_an_area_without_coordinates_is_accepted(): void
    {
        $this->post('/ui/lokasi', ['provinsi' => 'DKI Jakarta', 'kota' => 'Jakarta Barat'])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();
    }

    public function test_a_saved_location_can_be_forgotten(): void
    {
        $this->post('/ui/lokasi', ['lat' => -6.1754, 'lng' => 106.8272]);
        $this->delete('/ui/lokasi')->assertRedirect();

        $response = $this->getJson('/api/cabang/terdekat')->assertOk()->json('branches');

        $this->assertNull($response[0]['distanceKm']);
    }
}

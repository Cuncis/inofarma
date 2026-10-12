<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Database\Seeders\BranchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PickupLocationsTest extends TestCase
{
    use RefreshDatabase;

    private function stocked(Branch $branch, Product $product, int $quantity = 10): void
    {
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => $quantity]);
    }

    public function test_every_branch_is_listed_for_a_product_even_when_some_have_no_coordinates(): void
    {
        $product = Product::factory()->create(['sku' => 'PRD-LOC']);
        $near = Branch::factory()->create(['name' => 'Dekat', 'latitude' => -6.2000000, 'longitude' => 106.8000000]);
        $unplaced = Branch::factory()->withoutCoordinates()->create(['name' => 'Belum Ada Koordinat']);
        $this->stocked($near, $product);
        $this->stocked($unplaced, $product);

        $branches = $this->getJson('/api/cabang/untuk-produk/PRD-LOC?lat=-6.2100000&lng=106.8100000')
            ->assertOk()
            ->json('branches');

        $this->assertCount(2, $branches);
        $byName = collect($branches)->keyBy('name');
        $this->assertNotNull($byName['Dekat']['distanceKm']);
        $this->assertNull($byName['Belum Ada Koordinat']['distanceKm']);
        $this->assertSame('Dekat', $branches[0]['name']);
    }

    public function test_the_pickup_list_carries_a_full_address_for_the_popup(): void
    {
        $product = Product::factory()->create(['sku' => 'PRD-ADDR']);
        $branch = Branch::factory()->create(['address_line' => 'Jl. Contoh No. 1', 'kota' => 'Jakarta Timur']);
        $this->stocked($branch, $product);

        $this->getJson('/api/cabang/untuk-produk/PRD-ADDR')
            ->assertOk()
            ->assertJsonPath('branches.0.fullAddress', $branch->full_address)
            ->assertJsonPath('branches.0.supportsPickup', true);
    }

    public function test_a_postal_code_is_turned_into_a_point(): void
    {
        Cache::flush();
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '-6.2615', 'lon' => '106.9040', 'display_name' => 'Pisangan Lama, Jakarta Timur, 13230'],
        ])]);

        $this->getJson('/api/lokasi/kode-pos?kode=13230')
            ->assertOk()
            ->assertJsonPath('lat', -6.2615)
            ->assertJsonPath('lng', 106.904);

        // A second lookup is served from the cache, not from Nominatim again.
        $this->getJson('/api/lokasi/kode-pos?kode=13230')->assertOk();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['postalcode'] === '13230' && $request['countrycodes'] === 'id');
    }

    public function test_an_unknown_or_malformed_postal_code_is_refused(): void
    {
        Cache::flush();
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([])]);

        $this->getJson('/api/lokasi/kode-pos?kode=00000')->assertNotFound();
        $this->getJson('/api/lokasi/kode-pos?kode=abc')->assertStatus(422);
        $this->getJson('/api/lokasi/kode-pos')->assertStatus(422);
    }

    public function test_an_unreachable_geocoder_is_a_not_found_not_a_crash(): void
    {
        Cache::flush();
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response('', 503)]);

        $this->getJson('/api/lokasi/kode-pos?kode=11111')->assertNotFound();
    }

    public function test_only_pisangan_lama_delivers_and_all_four_branches_offer_pickup(): void
    {
        $this->seed(BranchSeeder::class);

        $delivering = Branch::where('supports_delivery', true)->pluck('name')->all();

        $this->assertSame(['Apotek Inofarma Pisangan Lama'], $delivering);
        $this->assertSame(4, Branch::where('supports_pickup', true)->count());
    }
}

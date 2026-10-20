<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Each apotek's own page (`/apotek/{slug}`), the homepage cards that lead to
 * it, and the redirect left where the "Cabang Kami" list used to be.
 */
class BranchPageTest extends TestCase
{
    use RefreshDatabase;

    /** The four seeded apotek, at their real coordinates. */
    private const COORDINATES = [
        'jengki' => [-6.2724794, 106.8796859],
        'kalisari' => [-6.3332296, 106.8572134],
        'kayu-manis' => [-6.2810388, 106.8519002],
        'pisangan-lama' => [-6.2099284, 106.8787117],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        foreach (self::COORDINATES as $slug => [$latitude, $longitude]) {
            Branch::where('slug', $slug)->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'maps_url' => "https://www.google.com/maps?q={$latitude},{$longitude}",
            ]);
        }
    }

    public function test_the_homepage_shows_a_card_per_apotek_with_a_photo(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/Home')
                ->has('pharmacies', 4)
                ->where('pharmacies', fn ($cards) => collect($cards)->pluck('slug')->sort()->values()->all() === array_keys(self::COORDINATES))
                ->where('pharmacies.0.photoUrl', fn (string $url) => str_starts_with($url, '/media/images/apotek/'))
            );
    }

    public function test_the_homepage_cards_are_nearest_first_once_the_location_is_known(): void
    {
        $this->post('/lokasi', ['lat' => -6.3332, 'lng' => 106.8572]);

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('pharmacies.0.slug', 'kalisari')
            );
    }

    public function test_a_branch_without_a_photo_or_not_active_has_no_card(): void
    {
        Branch::where('slug', 'jengki')->update(['photo_path' => null]);
        Branch::where('slug', 'kalisari')->update(['status' => 'tutup sementara']);

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('pharmacies', fn ($cards) => collect($cards)->pluck('slug')->sort()->values()->all() === ['kayu-manis', 'pisangan-lama'])
            );
    }

    public function test_the_apotek_page_shows_its_licences_pharmacist_and_hours(): void
    {
        $this->get('/apotek/kayu-manis')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/BranchDetail')
                ->where('branch.name', 'Apotek Inofarma Kayu Manis')
                ->where('branch.siaNumber', '30092400346910004')
                ->where('branch.apjSipaNumber', '15/B.19/31.75.04.1005.38.R-1/3/TM.09.19/e/2025')
                ->where('branch.apjName', 'apt. Nadia Tria Nurchasanah, S.Farm')
                ->where('branch.practiceSchedule', 'Senin - Jumat (08.00-21.00) Shift')
                ->where('branch.storeSchedule', 'Senin - Jumat (08.00-21.00) Shift')
                ->where('branch.fullAddress', fn (string $address) => str_contains($address, 'Kayu Manis'))
                ->where('branch.mapsUrl', 'https://www.google.com/maps?q=-6.2810388,106.8519002')
            );
    }

    public function test_the_other_apotek_are_listed_nearest_first_without_the_current_one(): void
    {
        // From Kayu Manis: Jengki is ~3 km away, Kalisari ~6 km, Pisangan Lama ~8 km.
        $this->get('/apotek/kayu-manis')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('nearby', 3)
                ->where('nearby.0.slug', 'jengki')
                ->where('nearby', fn ($cards) => ! collect($cards)->contains('slug', 'kayu-manis'))
                ->where('nearby.0.apjName', 'apt. Yulita Fitri Rosanti, S.Farm')
            );
    }

    public function test_contact_uses_the_branch_whatsapp_then_its_phone_then_the_company_number(): void
    {
        $this->get('/apotek/pisangan-lama')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('branch.contactUrl', 'https://wa.me/6281214018964'));

        Branch::where('slug', 'jengki')->update(['whatsapp' => null, 'phone' => '+62 21 5551 0001']);

        $this->get('/apotek/jengki')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('branch.contactUrl', 'tel:+622155510001'));

        Branch::where('slug', 'jengki')->update(['phone' => null]);

        $this->get('/apotek/jengki')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('branch.contactUrl', 'https://wa.me/6281214018964'));
    }

    public function test_a_temporarily_closed_apotek_still_has_its_page(): void
    {
        Branch::where('slug', 'jengki')->update(['status' => 'tutup sementara']);

        $this->get('/apotek/jengki')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('branch.status', 'tutup sementara'));
    }

    public function test_a_permanently_closed_or_unknown_apotek_is_a_404(): void
    {
        Branch::where('slug', 'jengki')->update(['status' => 'tutup permanen']);

        $this->get('/apotek/jengki')->assertNotFound();
        $this->get('/apotek/tidak-ada')->assertNotFound();
    }

    public function test_the_old_branch_list_address_leads_to_the_homepage(): void
    {
        $this->get('/cabang-kami')->assertStatus(301)->assertRedirect('/');
    }
}

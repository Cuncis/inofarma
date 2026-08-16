<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * The storefront and the admin must agree.
 *
 * Until Fase 1.5 the shop read a static JavaScript catalogue while the admin
 * read the database, so an edit in one never showed up in the other. These
 * tests exist to stop that coming back.
 */
class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_the_catalogue_is_shared_with_every_storefront_screen(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT)
                ->has('catalog.categories', self::CATEGORY_COUNT)
                ->where('catalog.products.0.name', 'Paracetamol 500mg')
                ->where('catalog.products.0.price', 12500)
            );

        // Also on a screen deep in the flow, because the search overlay lives in
        // the layout and can be opened from anywhere.
        $this->get('/ui/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT)
            );
    }

    public function test_a_price_changed_in_the_admin_shows_up_in_the_shop(): void
    {
        $this->signInAsAdmin();

        $this->put('/admin/produk/PRD-001', [
            'name' => 'Paracetamol 500mg',
            'category' => 'Obat Bebas',
            'seller' => 'Apotek Sehat Bersama',
            'unit' => 'Strip',
            'status' => 'Aktif',
            'price' => 13750,
            'oldPrice' => 15000,
            'prescription' => false,
            'blurb' => 'Meredakan demam dan nyeri ringan.',
        ])->assertSessionHasNoErrors();

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('catalog.products.0.name', 'Paracetamol 500mg')
                ->where('catalog.products.0.price', 13750)
                ->where('catalog.products.0.oldPrice', 15000)
            );
    }

    public function test_a_product_added_in_the_admin_appears_in_the_shop(): void
    {
        $this->signInAsAdmin();

        $this->post('/admin/produk', [
            'name' => 'Ibuprofen 400mg',
            'category' => 'Obat Bebas',
            'seller' => 'Apotek Sehat Bersama',
            'unit' => 'Strip',
            'status' => 'Aktif',
            'price' => 17500,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => 'Meredakan nyeri dan peradangan ringan.',
        ])->assertSessionHasNoErrors();

        $this->get('/ui/shop')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT + 1)
                ->where('catalog.products.12.name', 'Ibuprofen 400mg')
            );
    }

    public function test_a_deactivated_product_leaves_the_shop_but_stays_in_the_admin(): void
    {
        $this->signInAsAdmin();

        $this->put('/admin/produk/PRD-006', [
            'name' => 'Termometer Digital',
            'category' => 'Alat Kesehatan',
            'seller' => 'Farmasi Nusantara',
            'unit' => 'Pcs',
            'status' => 'Nonaktif',
            'price' => 125000,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => '',
        ])->assertSessionHasNoErrors();

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT - 1)
            );

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', self::PRODUCT_COUNT)
            );
    }

    public function test_the_shop_reports_availability_rather_than_catalogue_status(): void
    {
        // Vitamin C is out of stock at every branch. The shop should say so,
        // not repeat the "Aktif" the administrator set.
        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('catalog.products.2.name', 'Vitamin C 1000mg')
                ->where('catalog.products.2.status', 'Habis')
                ->where('catalog.products.2.stock', 0)
            );
    }

    public function test_category_counts_follow_the_catalogue(): void
    {
        $this->get('/ui/categories')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('catalog.categories.0.name', 'Obat Bebas')
                ->where('catalog.categories.0.products', 3)
            );
    }
}

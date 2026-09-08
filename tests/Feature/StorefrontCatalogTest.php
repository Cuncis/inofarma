<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
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
                // PRD-001 is seeded with a second photo specifically to
                // exercise the "several photos" gallery with real seed data.
                ->has('catalog.products.0.images', 2)
                ->has('catalog.products.1.images', 1)
            );

        // Also on a screen deep in the flow, because the search overlay lives in
        // the layout and can be opened from anywhere. Profile is
        // customer-gated, so this needs a signed-in shopper.
        $this->actingAs(Customer::factory()->create(['status' => 'aktif']), 'customer');

        $this->get('/ui/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT)
            );
    }

    public function test_a_price_changed_in_the_admin_shows_up_in_the_shop(): void
    {
        $this->signInAsAdmin();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm([
                'name' => 'Paracetamol 500mg',
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'unit' => 'Strip',
                'status' => 'aktif',
                'price' => 13750,
                'old_price' => 15000,
                'requires_prescription' => false,
                'blurb' => 'Meredakan demam dan nyeri ringan.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

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
        $category = Category::where('name', 'Obat Bebas')->firstOrFail();
        $seller = Supplier::where('name', 'Apotek Sehat Bersama')->firstOrFail();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Ibuprofen 400mg',
                'category_id' => $category->id,
                'supplier_id' => $seller->id,
                'unit' => 'Strip',
                'status' => 'aktif',
                'price' => 17500,
                'old_price' => null,
                'requires_prescription' => false,
                'blurb' => 'Meredakan nyeri dan peradangan ringan.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/ui/shop')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT + 1)
                ->where('catalog.products.12.name', 'Ibuprofen 400mg')
            );
    }

    public function test_a_deactivated_product_leaves_the_shop_but_stays_in_the_admin(): void
    {
        $this->signInAsAdmin();
        $product = Product::where('sku', 'PRD-006')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm([
                'name' => 'Termometer Digital',
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'unit' => 'Pcs',
                'status' => 'nonaktif',
                'price' => 125000,
                'old_price' => null,
                'requires_prescription' => false,
                'blurb' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('catalog.products', self::PRODUCT_COUNT - 1)
            );

        Livewire::test(ListProducts::class)
            ->assertCountTableRecords(self::PRODUCT_COUNT);
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
                ->where('catalog.categories.0.name', 'Kesehatan')
                ->where('catalog.categories.0.products', 1)
            );
    }
}

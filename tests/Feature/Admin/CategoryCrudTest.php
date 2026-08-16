<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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
            'name' => 'Perawatan Bayi',
            'slug' => 'perawatan-bayi',
            'status' => 'Aktif',
            'description' => 'Produk kebersihan dan perawatan khusus bayi.',
        ], $overrides);
    }

    public function test_the_list_is_seeded_and_carries_product_counts(): void
    {
        $this->get('/admin/kategori')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/CategoryList')
                ->has('categories', self::CATEGORY_COUNT)
                ->where('categories.0.name', 'Obat Bebas')
                // three seed products sit in Obat Bebas
                ->where('categories.0.products', 3)
            );
    }

    public function test_a_category_can_be_created(): void
    {
        $this->post('/admin/kategori', $this->validPayload())
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $this->get('/admin/kategori')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('categories', self::CATEGORY_COUNT + 1)
                ->where('categories.6.name', 'Perawatan Bayi')
                ->where('categories.6.slug', 'perawatan-bayi')
                ->where('categories.6.products', 0)
            );
    }

    public function test_a_slug_is_generated_when_left_blank(): void
    {
        $this->post('/admin/kategori', $this->validPayload([
            'name' => 'Nutrisi Lansia',
            'slug' => '',
        ]))->assertSessionHasNoErrors();

        $this->get('/admin/kategori/nutrisi-lansia')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('category.name', 'Nutrisi Lansia')
            );
    }

    public function test_a_duplicate_slug_gets_a_suffix_instead_of_colliding(): void
    {
        $this->post('/admin/kategori', $this->validPayload(['name' => 'Vitamin A', 'slug' => 'suplemen']));

        $this->get('/admin/kategori/suplemen-2')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('category.name', 'Vitamin A')
            );
    }

    public function test_a_category_can_be_read_with_its_products(): void
    {
        $this->get('/admin/kategori/suplemen')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/CategoryDetail')
                ->where('category.name', 'Suplemen')
                ->has('products', 2)
                ->where('products.0.name', 'Vitamin C 1000mg')
            );
    }

    public function test_an_unknown_category_is_a_404(): void
    {
        $this->get('/admin/kategori/tidak-ada')->assertNotFound();
        $this->get('/admin/kategori/tidak-ada/ubah')->assertNotFound();
    }

    public function test_a_category_can_be_updated(): void
    {
        $this->put('/admin/kategori/antiseptik', $this->validPayload([
            'name' => 'Antiseptik & Desinfektan',
            'slug' => 'antiseptik',
            'status' => 'Nonaktif',
        ]))
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $this->get('/admin/kategori/antiseptik')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('category.name', 'Antiseptik & Desinfektan')
                ->where('category.status', 'Nonaktif')
            );
    }

    public function test_renaming_a_category_carries_through_to_its_products(): void
    {
        // No cascade to run any more — products hold a foreign key, so the new
        // name is simply what the relation reads back.
        $this->put('/admin/kategori/antiseptik', $this->validPayload([
            'name' => 'Antiseptik & Desinfektan',
            'slug' => 'antiseptik',
        ]));

        $this->get('/admin/produk/PRD-005')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.category', 'Antiseptik & Desinfektan')
            );

        $this->get('/admin/kategori/antiseptik')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products', 2));
    }

    public function test_a_category_still_holding_products_cannot_be_deleted(): void
    {
        $this->delete('/admin/kategori/suplemen')
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('error');

        $this->get('/admin/kategori')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('categories', self::CATEGORY_COUNT)
            );
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $this->post('/admin/kategori', $this->validPayload());

        $this->delete('/admin/kategori/perawatan-bayi')
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $this->get('/admin/kategori/perawatan-bayi')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/kategori', [
            'name' => '',
            'slug' => 'Huruf Besar Dan Spasi',
            'status' => 'Entah',
        ])->assertSessionHasErrors(['name', 'slug', 'status']);

        $this->get('/admin/kategori')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('categories', self::CATEGORY_COUNT)
            );
    }

    public function test_a_category_name_cannot_be_duplicated(): void
    {
        $this->post('/admin/kategori', $this->validPayload(['name' => 'Suplemen', 'slug' => 'suplemen-baru']))
            ->assertSessionHasErrors(['name' => 'Kategori dengan nama ini sudah ada.']);
    }

    public function test_a_category_keeps_its_own_name_when_edited(): void
    {
        $this->put('/admin/kategori/suplemen', $this->validPayload([
            'name' => 'Suplemen',
            'slug' => 'suplemen',
            'description' => 'Deskripsi baru.',
        ]))->assertSessionHasNoErrors();
    }

    public function test_a_new_category_becomes_selectable_on_the_product_form(): void
    {
        $this->post('/admin/kategori', $this->validPayload());

        $this->get('/admin/produk/tambah')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('categories', self::CATEGORY_COUNT + 1)
            );

        // …and a product may then be assigned to it.
        $this->post('/admin/produk', [
            'name' => 'Sabun Bayi 200ml',
            'category' => 'Perawatan Bayi',
            'seller' => 'Apotek Sehat Bersama',
            'unit' => 'Botol',
            'status' => 'Aktif',
            'price' => 27000,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => 'Sabun cair lembut untuk kulit bayi.',
        ])->assertSessionHasNoErrors();
    }

    public function test_the_list_can_be_reset(): void
    {
        $this->post('/admin/kategori', $this->validPayload());
        $this->post('/admin/kategori/reset')->assertSessionHas('success');

        $this->get('/admin/kategori')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('categories', self::CATEGORY_COUNT)
            );
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Support\Catalog;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInAsAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ibuprofen 400mg',
            'category' => 'Obat Bebas',
            'seller' => 'Apotek Sehat Bersama',
            'unit' => 'Strip',
            'status' => 'Aktif',
            'price' => 17500,
            'oldPrice' => null,
            'stock' => 240,
            'prescription' => false,
            'blurb' => 'Meredakan nyeri dan peradangan ringan.',
        ], $overrides);
    }

    public function test_the_list_is_seeded_from_the_catalogue(): void
    {
        $this->get('/admin/produk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ProductList')
                ->has('products', count(Catalog::products()))
                ->has('categories', count(Catalog::categories()))
                ->where('products.0.name', 'Paracetamol 500mg')
            );
    }

    public function test_a_product_can_be_created_and_appears_in_the_list(): void
    {
        $this->post('/admin/produk', $this->validPayload())
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', count(Catalog::products()) + 1)
                ->where('products.12.name', 'Ibuprofen 400mg')
                ->where('products.12.id', 'PRD-013')
                ->where('products.12.price', 17500)
            );
    }

    public function test_a_product_can_be_read_by_id(): void
    {
        $this->get('/admin/produk/PRD-003')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ProductDetail')
                ->where('product.name', 'Vitamin C 1000mg')
                ->where('product.status', 'Habis')
            );
    }

    public function test_an_unknown_product_is_a_404(): void
    {
        $this->get('/admin/produk/PRD-999')->assertNotFound();
        $this->get('/admin/produk/PRD-999/ubah')->assertNotFound();
    }

    public function test_a_product_can_be_updated(): void
    {
        $this->put('/admin/produk/PRD-001', $this->validPayload([
            'name' => 'Paracetamol 650mg',
            'price' => 14000,
            'stock' => 500,
        ]))
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk/PRD-001')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.name', 'Paracetamol 650mg')
                ->where('product.price', 14000)
                ->where('product.stock', 500)
                // untouched fields survive the merge
                ->where('product.sold', 1240)
            );
    }

    public function test_a_product_can_be_deleted(): void
    {
        $this->delete('/admin/produk/PRD-002')
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', count(Catalog::products()) - 1)
            );

        $this->get('/admin/produk/PRD-002')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/produk', [
            'name' => '',
            'category' => 'Bukan Kategori',
            'unit' => 'Karung',
            'status' => 'Entah',
            'seller' => 'Toko Fiktif',
            'price' => -5,
            'stock' => 'banyak',
            'prescription' => 'mungkin',
        ])->assertSessionHasErrors([
            'name', 'category', 'seller', 'unit', 'status', 'price', 'stock', 'prescription',
        ]);

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', count(Catalog::products()))
            );
    }

    public function test_the_struck_through_price_must_beat_the_selling_price(): void
    {
        $this->post('/admin/produk', $this->validPayload([
            'price' => 20000,
            'oldPrice' => 15000,
        ]))->assertSessionHasErrors('oldPrice');
    }

    public function test_validation_messages_come_back_in_indonesian(): void
    {
        $this->post('/admin/produk', $this->validPayload(['name' => '']))
            ->assertSessionHasErrors(['name' => 'Kolom nama produk wajib diisi.']);
    }

    public function test_the_catalogue_can_be_reset_after_edits(): void
    {
        $this->delete('/admin/produk/PRD-001');
        $this->delete('/admin/produk/PRD-002');

        $this->post('/admin/produk/reset')
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', count(Catalog::products()))
                ->where('products.0.name', 'Paracetamol 500mg')
            );
    }

    public function test_the_grid_view_is_now_a_mode_of_the_list_screen(): void
    {
        // The separate /produk/grid route was folded into the list screen's
        // view toggle, so it should no longer resolve.
        $this->get('/admin/produk/grid')->assertNotFound();
    }

    public function test_the_edit_screen_carries_the_record_and_its_options(): void
    {
        $this->get('/admin/produk/PRD-004/ubah')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ProductEdit')
                ->where('product.name', 'Masker Medis 3 Ply')
                ->has('categories')
                ->has('units')
                ->has('statuses')
            );
    }
}

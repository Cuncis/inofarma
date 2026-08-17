<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    /**
     * There is deliberately no `stock` key: stock belongs to a product at a
     * branch, so the product form has no field for it.
     *
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
                ->has('products', self::PRODUCT_COUNT)
                ->has('categories', self::CATEGORY_COUNT)
                ->where('products.0.name', 'Paracetamol 500mg')
            );
    }

    public function test_the_listed_stock_is_the_sum_across_every_branch(): void
    {
        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('products.0.stock', 482)
                ->where('products.0.stockStatus', 'Tersedia')
            );
    }

    public function test_a_product_can_be_created_and_appears_in_the_list(): void
    {
        $this->post('/admin/produk', $this->validPayload())
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', self::PRODUCT_COUNT + 1)
                ->where('products.12.name', 'Ibuprofen 400mg')
                ->where('products.12.id', 'PRD-013')
                ->where('products.12.price', 17500)
                // Nothing has been put on a shelf yet.
                ->where('products.12.stock', 0)
                ->where('products.12.stockStatus', 'Habis')
            );
    }

    public function test_a_product_can_be_read_by_id(): void
    {
        // Vitamin C is out of stock everywhere, but the product itself is still
        // active — those are two different facts and two different fields.
        $this->get('/admin/produk/PRD-003')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ProductDetail')
                ->where('product.name', 'Vitamin C 1000mg')
                ->where('product.status', 'Aktif')
                ->where('product.stockStatus', 'Habis')
                ->where('product.stock', 0)
            );
    }

    public function test_the_detail_screen_breaks_stock_down_by_branch(): void
    {
        $this->get('/admin/produk/PRD-001')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('product.branches', self::BRANCH_COUNT)
                ->has('product.branches.0.available')
                ->has('product.branches.0.name')
            );
    }

    public function test_the_same_product_holds_different_stock_at_different_branches(): void
    {
        $response = $this->get('/admin/produk/PRD-001');

        $quantities = collect($response->viewData('page')['props']['product']['branches'])
            ->pluck('quantity');

        $this->assertGreaterThan(1, $quantities->unique()->count(),
            'Stok seharusnya berbeda antar cabang, bukan angka yang sama di semua tempat.');
        $this->assertSame(482, $quantities->sum());
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
        ]))
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('success');

        $this->get('/admin/produk/PRD-001')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.name', 'Paracetamol 650mg')
                ->where('product.price', 14000)
                // Editing the product does not disturb what is on the shelves.
                ->where('product.stock', 482)
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
                ->has('products', self::PRODUCT_COUNT - 1)
            );

        $this->get('/admin/produk/PRD-002')->assertNotFound();
    }

    public function test_deleting_a_product_leaves_past_orders_intact(): void
    {
        // PRD-002 was bought on INO-2446. Removing it from the catalogue must
        // not change what that order says somebody paid.
        $this->delete('/admin/produk/PRD-002');

        $this->get('/admin/pesanan/INO-2446')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.items.0.name', 'Amoxicillin 500mg')
                ->where('order.items.0.price', 38000)
                ->where('order.total', 308000)
            );
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
            'prescription' => 'mungkin',
        ])->assertSessionHasErrors([
            'name', 'category', 'seller', 'unit', 'status', 'price', 'prescription',
        ]);

        $this->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('products', self::PRODUCT_COUNT)
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
                ->has('products', self::PRODUCT_COUNT)
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
                ->has('drugClasses')
                ->has('storageConditions')
            );
    }

    public function test_a_product_can_be_created_with_full_pharmacy_data(): void
    {
        $this->post('/admin/produk', $this->validPayload([
            'name' => 'Loratadine 10mg',
            'drugClass' => 'Bebas',
            'nie' => 'DKL1234567890A1',
            'composition' => 'Loratadine 10 mg per tablet.',
            'indication' => 'Meredakan gejala alergi.',
            'dosage' => '1 tablet per hari.',
            'sideEffects' => 'Mengantuk ringan.',
            'manufacturer' => 'PT Kalbe Farma Tbk',
            'maxQtyPerOrder' => 3,
            'storage' => 'Suhu Ruang',
            'weightGrams' => 20,
        ]))->assertSessionHasNoErrors();

        $this->get('/admin/produk/PRD-013')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.drugClass', 'Bebas')
                ->where('product.nie', 'DKL1234567890A1')
                ->where('product.manufacturer', 'PT Kalbe Farma Tbk')
                ->where('product.maxQtyPerOrder', 3)
                ->where('product.storage', 'Suhu Ruang')
                ->where('product.weightGrams', 20)
            );
    }

    public function test_a_product_without_pharmacy_data_gets_safe_defaults(): void
    {
        $this->post('/admin/produk', $this->validPayload(['name' => 'Kapas Bulat 50g']))
            ->assertSessionHasNoErrors();

        $this->get('/admin/produk/PRD-013')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.drugClass', 'Non-Obat')
                ->where('product.storage', 'Suhu Ruang')
                ->where('product.needsWarningLabel', false)
            );
    }

    public function test_bebas_terbatas_requires_a_warning(): void
    {
        $this->post('/admin/produk', $this->validPayload([
            'name' => 'Obat Flu Malam',
            'drugClass' => 'Bebas Terbatas',
        ]))->assertSessionHasErrors(['warning' => 'Obat bebas terbatas wajib menampilkan peringatan P1–P6.']);
    }

    public function test_the_seeded_cough_syrup_carries_its_p1_warning(): void
    {
        $this->get('/admin/produk/PRD-010')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.drugClass', 'Bebas Terbatas')
                ->where('product.needsWarningLabel', true)
                ->where('product.warning', 'Awas! Obat Keras. Bacalah aturan pemakaiannya.')
            );
    }

    public function test_the_storefront_catalogue_carries_pharmacy_data(): void
    {
        $this->get('/ui/product-detail?id=PRD-010')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('catalog.products.9.drugClass', 'Bebas Terbatas')
                ->where('catalog.products.9.needsWarningLabel', true)
            );
    }
}

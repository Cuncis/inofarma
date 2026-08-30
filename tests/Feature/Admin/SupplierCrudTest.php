<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
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
            'name' => 'Apotek Cendana',
            'owner' => 'Putri Maharani',
            'email' => 'apotekcendana@mail.com',
            'phone' => '+62 361 5556 0006',
            'license' => 'SIA/2025/00399',
            'city' => 'Denpasar',
            'address' => 'Jl. Sunset Road No. 12',
            'status' => 'Aktif',
        ], $overrides);
    }

    public function test_the_list_derives_product_counts_and_revenue(): void
    {
        $this->get('/admin/pemasok')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/SupplierList')
                ->has('suppliers', self::SUPPLIER_COUNT)
                ->where('suppliers.0.name', 'Apotek Sehat Bersama')
                ->where('suppliers.0.products', 4)
                // 12500*1240 + 38000*860 + 24000*1580 + 29500*940
                ->where('suppliers.0.revenue', 113830000)
            );
    }

    public function test_a_seller_with_no_products_reports_zero(): void
    {
        $this->get('/admin/pemasok')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('suppliers.4.name', 'Apotek Melati')
                ->where('suppliers.4.products', 0)
                ->where('suppliers.4.revenue', 0)
            );
    }

    public function test_a_seller_can_be_created(): void
    {
        $this->post('/admin/pemasok', $this->validPayload())
            ->assertRedirect(route('admin.pemasok.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pemasok')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('suppliers', self::SUPPLIER_COUNT + 1)
                ->where('suppliers.5.name', 'Apotek Cendana')
                ->where('suppliers.5.id', 'SEL-006')
                ->where('suppliers.5.products', 0)
            );
    }

    public function test_a_seller_can_be_read_with_only_their_products(): void
    {
        $this->get('/admin/pemasok/SEL-002')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/SupplierDetail')
                ->where('supplier.name', 'Toko Obat Mandiri')
                ->has('products', 2)
                ->where('products.0.name', 'Vitamin C 1000mg')
            );
    }

    public function test_an_unknown_seller_is_a_404(): void
    {
        $this->get('/admin/pemasok/SEL-999')->assertNotFound();
        $this->get('/admin/pemasok/SEL-999/ubah')->assertNotFound();
    }

    public function test_a_seller_can_be_updated(): void
    {
        $this->put('/admin/pemasok/SEL-004', $this->validPayload([
            'name' => 'Griya Farma',
            'email' => 'griyafarma@mail.com',
            'license' => 'SIA/2025/00077',
            'city' => 'Sleman',
            'status' => 'Nonaktif',
        ]))
            ->assertRedirect(route('admin.pemasok.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pemasok/SEL-004')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('supplier.city', 'Sleman')
                ->where('supplier.status', 'Nonaktif')
                ->has('products', 3)
            );
    }

    public function test_renaming_a_seller_carries_through_to_their_products(): void
    {
        // Products hold a foreign key, so the rename needs no cascade.
        $this->put('/admin/pemasok/SEL-002', $this->validPayload([
            'name' => 'Toko Obat Mandiri Jaya',
            'email' => 'obatmandiri@mail.com',
            'license' => 'SIA/2024/00224',
        ]))->assertSessionHasNoErrors();

        $this->get('/admin/produk/PRD-003')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.seller', 'Toko Obat Mandiri Jaya')
            );

        $this->get('/admin/pemasok/SEL-002')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products', 2));
    }

    public function test_a_seller_still_stocking_products_cannot_be_deleted(): void
    {
        $this->delete('/admin/pemasok/SEL-001')
            ->assertRedirect(route('admin.pemasok.index'))
            ->assertSessionHas('error');

        $this->get('/admin/pemasok')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('suppliers', self::SUPPLIER_COUNT)
            );
    }

    public function test_a_seller_with_no_products_can_be_deleted(): void
    {
        $this->delete('/admin/pemasok/SEL-005')
            ->assertRedirect(route('admin.pemasok.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pemasok/SEL-005')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/pemasok', [
            'name' => '',
            'owner' => '',
            'email' => 'bukan-email',
            'phone' => 'telepon saya',
            'license' => '',
            'city' => '',
            'status' => 'Entah',
        ])->assertSessionHasErrors(['name', 'owner', 'email', 'phone', 'license', 'city', 'status']);
    }

    public function test_name_email_and_licence_must_each_be_unique(): void
    {
        $this->post('/admin/pemasok', $this->validPayload(['name' => 'Farmasi Nusantara']))
            ->assertSessionHasErrors(['name' => 'Nama toko ini sudah terdaftar.']);

        $this->post('/admin/pemasok', $this->validPayload(['email' => 'griyafarma@mail.com']))
            ->assertSessionHasErrors(['email' => 'Email ini sudah dipakai pemasok lain.']);

        $this->post('/admin/pemasok', $this->validPayload(['license' => 'SIA/2024/00181']))
            ->assertSessionHasErrors(['license' => 'Nomor izin apotek ini sudah terdaftar.']);
    }

    public function test_a_new_seller_becomes_selectable_on_the_product_form(): void
    {
        $this->post('/admin/pemasok', $this->validPayload());

        $this->get('/admin/produk/tambah')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('sellers', self::SUPPLIER_COUNT + 1)
            );

        $this->post('/admin/produk', [
            'name' => 'Betadine 30ml',
            'category' => 'Kebutuhan Keluarga',
            'seller' => 'Apotek Cendana',
            'unit' => 'Botol',
            'status' => 'Aktif',
            'price' => 28000,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => 'Antiseptik untuk luka luar.',
        ])->assertSessionHasNoErrors();

        $this->get('/admin/pemasok/SEL-006')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products', 1));
    }

    public function test_a_product_cannot_name_an_unknown_seller(): void
    {
        $this->post('/admin/produk', [
            'name' => 'Produk Liar',
            'category' => 'Obat Bebas',
            'seller' => 'Apotek Tidak Terdaftar',
            'unit' => 'Strip',
            'status' => 'Aktif',
            'price' => 10000,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => '',
        ])->assertSessionHasErrors('seller');
    }

    public function test_the_list_can_be_reset(): void
    {
        $this->post('/admin/pemasok', $this->validPayload());
        $this->post('/admin/pemasok/reset')->assertSessionHas('success');

        $this->get('/admin/pemasok')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('suppliers', self::SUPPLIER_COUNT)
            );
    }
}

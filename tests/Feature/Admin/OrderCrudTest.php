<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class OrderCrudTest extends TestCase
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
            'customerEmail' => 'anisa.rahmawati@mail.com',
            'branch' => 'CB-001',
            'fulfilment' => 'Antar',
            'payment' => 'GoPay',
            'status' => 'Diproses',
            'shipping' => 20000,
            'note' => 'Titip di pos satpam.',
            'items' => [
                ['productId' => 'PRD-001', 'qty' => 4],
                ['productId' => 'PRD-006', 'qty' => 1],
            ],
        ], $overrides);
    }

    public function test_the_list_derives_totals_from_the_line_items(): void
    {
        $this->get('/admin/pesanan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/OrderList')
                ->has('orders', self::ORDER_COUNT)
                ->where('orders.0.id', 'INO-2451')
                ->where('orders.0.customerName', 'Kirana Wijaya')
                // 12*12500 + 6*45000 + 2*18500 = 457000, plus 25000 shipping
                ->where('orders.0.subtotal', 457000)
                ->where('orders.0.total', 482000)
                ->where('orders.0.itemCount', 20)
            );
    }

    public function test_every_order_names_the_branch_it_came_from(): void
    {
        $this->get('/admin/pesanan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('orders.0.branch')
                ->has('orders.0.branchName')
                ->where('orders.0.fulfilment', 'Antar')
            );
    }

    public function test_an_order_can_be_created_with_a_generated_number(): void
    {
        $this->post('/admin/pesanan', $this->validPayload())
            ->assertRedirect(route('admin.pesanan.index'))
            ->assertSessionHas('success');

        // 4*12500 + 1*125000 = 175000, plus 20000 shipping
        $this->get('/admin/pesanan/INO-2452')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.customerEmail', 'anisa.rahmawati@mail.com')
                ->where('order.subtotal', 175000)
                ->where('order.total', 195000)
                ->where('order.branch', 'CB-001')
            );
    }

    public function test_an_order_can_be_collected_at_the_branch(): void
    {
        $this->post('/admin/pesanan', $this->validPayload([
            'fulfilment' => 'Ambil',
            'shipping' => 0,
        ]))->assertSessionHasNoErrors();

        $this->get('/admin/pesanan/INO-2452')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.fulfilment', 'Ambil')
                ->where('order.total', 175000)
            );
    }

    public function test_an_order_must_name_a_real_branch(): void
    {
        $this->post('/admin/pesanan', $this->validPayload(['branch' => 'CB-999']))
            ->assertSessionHasErrors(['branch' => 'Cabang ini tidak terdaftar.']);
    }

    public function test_line_items_snapshot_the_product_name_and_price(): void
    {
        $this->post('/admin/pesanan', $this->validPayload());

        $this->get('/admin/pesanan/INO-2452')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.items.0.name', 'Paracetamol 500mg')
                ->where('order.items.0.price', 12500)
            );

        // Repricing the product must not rewrite what the order was charged.
        $this->put('/admin/produk/PRD-001', [
            'name' => 'Paracetamol 500mg Baru',
            'category' => 'Obat Bebas',
            'seller' => 'Apotek Sehat Bersama',
            'unit' => 'Strip',
            'status' => 'Aktif',
            'price' => 99000,
            'oldPrice' => null,
            'prescription' => false,
            'blurb' => '',
        ])->assertSessionHasNoErrors();

        $this->get('/admin/pesanan/INO-2452')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.items.0.name', 'Paracetamol 500mg')
                ->where('order.items.0.price', 12500)
                ->where('order.total', 195000)
            );
    }

    public function test_an_order_can_be_read_by_id(): void
    {
        $this->get('/admin/pesanan/INO-2450')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/OrderDetail')
                ->where('order.customerName', 'Rizky Ananda')
                ->where('order.status', 'Diproses')
                ->has('order.items', 2)
            );
    }

    public function test_an_unknown_order_is_a_404(): void
    {
        $this->get('/admin/pesanan/INO-9999')->assertNotFound();
        $this->get('/admin/pesanan/INO-9999/ubah')->assertNotFound();
    }

    public function test_an_order_can_be_updated(): void
    {
        $this->put('/admin/pesanan/INO-2450', $this->validPayload([
            'customerEmail' => 'rizky.ananda@mail.com',
            'status' => 'Dikirim',
            'shipping' => 30000,
            'items' => [['productId' => 'PRD-003', 'qty' => 2]],
        ]))
            ->assertRedirect(route('admin.pesanan.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pesanan/INO-2450')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('order.status', 'Dikirim')
                ->has('order.items', 1)
                // 2 * 75000 + 30000
                ->where('order.total', 180000)
            );
    }

    public function test_a_completed_order_cannot_be_deleted(): void
    {
        $this->delete('/admin/pesanan/INO-2451')
            ->assertRedirect(route('admin.pesanan.index'))
            ->assertSessionHas('error');

        $this->get('/admin/pesanan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('orders', self::ORDER_COUNT)
            );
    }

    public function test_an_unfinished_order_can_be_deleted(): void
    {
        $this->delete('/admin/pesanan/INO-2448')
            ->assertRedirect(route('admin.pesanan.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pesanan/INO-2448')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/pesanan', [
            'customerEmail' => 'tidak.terdaftar@mail.com',
            'branch' => '',
            'fulfilment' => 'Kirim Burung',
            'payment' => 'Barter',
            'status' => 'Entah',
            'shipping' => -1,
            'items' => [],
        ])->assertSessionHasErrors([
            'customerEmail', 'branch', 'fulfilment', 'payment', 'status', 'shipping', 'items',
        ]);
    }

    public function test_an_order_must_have_at_least_one_item(): void
    {
        $this->post('/admin/pesanan', $this->validPayload(['items' => []]))
            ->assertSessionHasErrors(['items' => 'Pesanan harus memiliki minimal satu item.']);
    }

    public function test_a_line_item_must_name_a_real_product(): void
    {
        $this->post('/admin/pesanan', $this->validPayload([
            'items' => [['productId' => 'PRD-999', 'qty' => 1]],
        ]))->assertSessionHasErrors('items.0.productId');
    }

    public function test_a_line_quantity_must_be_at_least_one(): void
    {
        $this->post('/admin/pesanan', $this->validPayload([
            'items' => [['productId' => 'PRD-001', 'qty' => 0]],
        ]))->assertSessionHasErrors('items.0.qty');
    }

    public function test_a_new_order_shows_up_on_the_customer(): void
    {
        // Anisa starts with no orders.
        $this->get('/admin/pelanggan/CUS-006')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('orders', 0));

        $this->post('/admin/pesanan', $this->validPayload());

        $this->get('/admin/pelanggan/CUS-006')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('orders', 1)
                ->where('customer.spent', 195000)
            );
    }

    public function test_deleting_an_order_updates_the_customer_stats(): void
    {
        $this->get('/admin/pelanggan/CUS-004')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('customer.orders', 1));

        $this->delete('/admin/pesanan/INO-2448');

        $this->get('/admin/pelanggan/CUS-004')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customer.orders', 0)
                ->where('customer.spent', 0)
            );
    }

    public function test_the_list_can_be_reset(): void
    {
        $this->delete('/admin/pesanan/INO-2448');
        $this->post('/admin/pesanan/reset')->assertSessionHas('success');

        $this->get('/admin/pesanan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('orders', self::ORDER_COUNT)
            );
    }
}

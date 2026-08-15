<?php

namespace Tests\Feature\Admin;

use App\Support\Catalog;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
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
            'name' => 'Putri Maharani',
            'email' => 'putri.maharani@mail.com',
            'phone' => '+62 819-8877-6655',
            'city' => 'Denpasar',
            'address' => 'Jl. Sunset Road No. 44',
            'status' => 'Aktif',
        ], $overrides);
    }

    public function test_the_list_is_seeded_and_derives_order_stats(): void
    {
        $this->get('/admin/pelanggan')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/CustomerList')
                ->has('customers', count(Catalog::customers()))
                ->where('customers.0.name', 'Kirana Wijaya')
                // two seed orders belong to her, totalling 482000 + 308000
                ->where('customers.0.orders', 2)
                ->where('customers.0.spent', 790000)
            );
    }

    public function test_a_customer_without_orders_reports_zero(): void
    {
        $this->get('/admin/pelanggan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customers.5.name', 'Anisa Rahmawati')
                ->where('customers.5.orders', 0)
                ->where('customers.5.spent', 0)
            );
    }

    public function test_a_customer_can_be_created(): void
    {
        $this->post('/admin/pelanggan', $this->validPayload())
            ->assertRedirect(route('admin.pelanggan.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pelanggan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('customers', count(Catalog::customers()) + 1)
                ->where('customers.6.name', 'Putri Maharani')
                ->where('customers.6.id', 'CUS-007')
                ->where('customers.6.orders', 0)
            );
    }

    public function test_a_customer_can_be_read_with_their_orders(): void
    {
        $this->get('/admin/pelanggan/CUS-003')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/CustomerDetail')
                ->where('customer.name', 'Dinda Puspita')
                ->has('orders', 2)
                ->where('orders.0.id', 'INO-2449')
            );
    }

    public function test_an_unknown_customer_is_a_404(): void
    {
        $this->get('/admin/pelanggan/CUS-999')->assertNotFound();
        $this->get('/admin/pelanggan/CUS-999/ubah')->assertNotFound();
    }

    public function test_a_customer_can_be_updated(): void
    {
        $this->put('/admin/pelanggan/CUS-002', $this->validPayload([
            'name' => 'Rizky Ananda Putra',
            'email' => 'rizky.ananda@mail.com',
            'city' => 'Bekasi',
            'status' => 'Nonaktif',
        ]))
            ->assertRedirect(route('admin.pelanggan.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pelanggan/CUS-002')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customer.name', 'Rizky Ananda Putra')
                ->where('customer.city', 'Bekasi')
                ->where('customer.status', 'Nonaktif')
                // keeping the email keeps the order history attached
                ->has('orders', 1)
            );
    }

    public function test_a_customer_with_orders_cannot_be_deleted(): void
    {
        $this->delete('/admin/pelanggan/CUS-001')
            ->assertRedirect(route('admin.pelanggan.index'))
            ->assertSessionHas('error');

        $this->get('/admin/pelanggan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('customers', count(Catalog::customers()))
            );
    }

    public function test_a_customer_without_orders_can_be_deleted(): void
    {
        $this->delete('/admin/pelanggan/CUS-006')
            ->assertRedirect(route('admin.pelanggan.index'))
            ->assertSessionHas('success');

        $this->get('/admin/pelanggan/CUS-006')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/pelanggan', [
            'name' => '',
            'email' => 'bukan-email',
            'phone' => 'nomor saya',
            'city' => '',
            'status' => 'Entah',
        ])->assertSessionHasErrors(['name', 'email', 'phone', 'city', 'status']);

        $this->get('/admin/pelanggan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('customers', count(Catalog::customers()))
            );
    }

    public function test_an_email_cannot_be_taken_twice(): void
    {
        $this->post('/admin/pelanggan', $this->validPayload(['email' => 'kirana.wijaya@mail.com']))
            ->assertSessionHasErrors(['email' => 'Email ini sudah dipakai pelanggan lain.']);
    }

    public function test_a_customer_keeps_their_own_email_when_edited(): void
    {
        $this->put('/admin/pelanggan/CUS-001', $this->validPayload([
            'name' => 'Kirana Wijaya',
            'email' => 'kirana.wijaya@mail.com',
        ]))->assertSessionHasNoErrors();
    }

    public function test_changing_the_email_detaches_the_order_history(): void
    {
        $this->put('/admin/pelanggan/CUS-001', $this->validPayload([
            'name' => 'Kirana Wijaya',
            'email' => 'kirana.baru@mail.com',
        ]))->assertSessionHasNoErrors();

        // Orders key off the email, so they no longer follow the record.
        $this->get('/admin/pelanggan/CUS-001')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customer.email', 'kirana.baru@mail.com')
                ->has('orders', 0)
            );
    }

    public function test_the_list_can_be_reset(): void
    {
        $this->post('/admin/pelanggan', $this->validPayload());
        $this->post('/admin/pelanggan/reset')->assertSessionHas('success');

        $this->get('/admin/pelanggan')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('customers', count(Catalog::customers()))
            );
    }
}

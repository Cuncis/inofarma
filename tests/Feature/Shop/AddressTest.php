<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_addresses_require_signing_in(): void
    {
        $this->get('/ui/my-address')->assertRedirect(route('ui.signin'));
    }

    public function test_a_customer_can_add_an_address_and_it_becomes_the_default(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        $this->post('/ui/add-new-address', [
            'label' => 'Rumah',
            'recipientName' => 'Kirana Wijaya',
            'phone' => '081234567890',
            'addressLine' => 'Jl. Kebon Jeruk Raya No. 27',
            'kota' => 'Jakarta Barat',
            'provinsi' => 'DKI Jakarta',
            'postalCode' => '11530',
            'latitude' => -6.2,
            'longitude' => 106.8,
        ])->assertRedirect(route('ui.my-address'));

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id, 'label' => 'Rumah', 'is_default' => true,
        ]);
    }

    public function test_a_second_address_is_not_the_default(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        CustomerAddress::factory()->for($customer)->isDefault()->create();

        $this->actingAs($customer, 'customer');

        $this->post('/ui/add-new-address', [
            'label' => 'Kantor', 'recipientName' => 'Kirana', 'phone' => '0812',
            'addressLine' => 'Jl. Sudirman', 'kota' => 'Jakarta Selatan', 'provinsi' => 'DKI Jakarta',
        ]);

        $this->get('/ui/my-address')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('addresses', 2)
        );

        $this->assertDatabaseHas('customer_addresses', ['label' => 'Kantor', 'is_default' => false]);
    }

    public function test_an_address_can_be_made_the_default(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $first = CustomerAddress::factory()->for($customer)->isDefault()->create();
        $second = CustomerAddress::factory()->for($customer)->create();

        $this->actingAs($customer, 'customer');
        $this->post("/ui/alamat/{$second->id}/utama")->assertSessionHas('success');

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_deleting_the_default_address_promotes_another_one(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $first = CustomerAddress::factory()->for($customer)->isDefault()->create();
        $second = CustomerAddress::factory()->for($customer)->create();

        $this->actingAs($customer, 'customer');
        $this->delete("/ui/alamat/{$first->id}")->assertSessionHas('success');

        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_a_customer_cannot_delete_another_customers_address(): void
    {
        $owner = Customer::factory()->create(['status' => 'aktif']);
        $intruder = Customer::factory()->create(['status' => 'aktif']);
        $address = CustomerAddress::factory()->for($owner)->create();

        $this->actingAs($intruder, 'customer');
        $this->delete("/ui/alamat/{$address->id}")->assertNotFound();

        $this->assertDatabaseHas('customer_addresses', ['id' => $address->id]);
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        $this->post('/ui/add-new-address', [])
            ->assertSessionHasErrors(['label', 'recipientName', 'phone', 'addressLine', 'kota', 'provinsi']);
    }
}

<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    private function makeChain(): array
    {
        $province = Region::factory()->create(['code' => '31', 'parent_code' => null, 'level' => 1, 'name' => 'DKI Jakarta']);
        $regency = Region::factory()->create(['code' => '31.71', 'parent_code' => '31', 'level' => 2, 'name' => 'Jakarta Pusat']);
        $district = Region::factory()->create(['code' => '31.71.01', 'parent_code' => '31.71', 'level' => 3, 'name' => 'Gambir']);
        $village = Region::factory()->create([
            'code' => '31.71.01.1001', 'parent_code' => '31.71.01', 'level' => 4,
            'name' => 'Gambir', 'postal_code' => '10110',
        ]);

        return compact('province', 'regency', 'district', 'village');
    }

    public function test_it_lists_top_level_provinces_when_no_parent_is_given(): void
    {
        $this->makeChain();
        Region::factory()->create(['code' => '99', 'parent_code' => null, 'level' => 1, 'name' => 'Zzz Province']);

        $this->actingAs(Customer::factory()->create(['status' => 'aktif']), 'customer');

        $this->get('/ui/wilayah')
            ->assertOk()
            ->assertJson(['options' => [
                ['code' => '31', 'name' => 'DKI Jakarta', 'postalCode' => null],
                ['code' => '99', 'name' => 'Zzz Province', 'postalCode' => null],
            ]]);
    }

    public function test_it_lists_the_children_of_a_given_parent(): void
    {
        $chain = $this->makeChain();

        $this->actingAs(Customer::factory()->create(['status' => 'aktif']), 'customer');

        $this->get('/ui/wilayah?parent=31')
            ->assertOk()
            ->assertJson(['options' => [
                ['code' => '31.71', 'name' => 'Jakarta Pusat', 'postalCode' => null],
            ]]);

        $this->get('/ui/wilayah?parent=31.71.01')
            ->assertOk()
            ->assertJson(['options' => [
                ['code' => '31.71.01.1001', 'name' => 'Gambir', 'postalCode' => '10110'],
            ]]);

        $this->assertNotNull($chain['village']);
    }

    public function test_add_new_address_receives_the_province_list(): void
    {
        $this->makeChain();

        $customer = Customer::factory()->create(['status' => 'aktif']);
        $this->actingAs($customer, 'customer');

        $this->get('/ui/add-new-address')->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/AddNewAddress')
            ->has('provinces', 1)
            ->where('provinces.0.code', '31')
        );
    }
}

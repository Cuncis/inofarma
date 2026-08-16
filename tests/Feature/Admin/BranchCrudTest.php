<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class BranchCrudTest extends TestCase
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
            'name' => 'Apotek Inofarma Cinere',
            'addressLine' => 'Jl. Cinere Raya No. 10',
            'kelurahan' => 'Cinere',
            'kecamatan' => 'Cinere',
            'kota' => 'Kota Depok',
            'provinsi' => 'Jawa Barat',
            'postalCode' => '16514',
            'latitude' => -6.3167,
            'longitude' => 106.7833,
            'phone' => '+62 21 7654 0001',
            'whatsapp' => '+62 812-0000-1111',
            'siaNumber' => 'SIA/2026/00500',
            'apjName' => 'Apt. Contoh Nama',
            'apjSipaNumber' => 'SIPA/2026/00500',
            'supportsDelivery' => true,
            'supportsPickup' => true,
            'deliveryRadiusKm' => 8,
            'status' => 'Aktif',
        ], $overrides);
    }

    public function test_the_list_is_seeded_with_the_ten_branches(): void
    {
        $this->get('/admin/cabang')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/BranchList')
                ->has('branches', self::BRANCH_COUNT)
            );
    }

    public function test_a_branch_can_be_created(): void
    {
        $this->post('/admin/cabang', $this->validPayload())
            ->assertRedirect(route('admin.cabang.index'))
            ->assertSessionHas('success');

        $this->get('/admin/cabang')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('branches', self::BRANCH_COUNT + 1)
            );
    }

    public function test_a_created_branch_can_be_found_by_its_generated_code(): void
    {
        $this->post('/admin/cabang', $this->validPayload());

        $this->get('/admin/cabang/CB-011')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('branch.name', 'Apotek Inofarma Cinere')
                ->where('branch.latitude', -6.3167)
                ->where('branch.longitude', 106.7833)
            );
    }

    public function test_an_unknown_branch_is_a_404(): void
    {
        $this->get('/admin/cabang/CB-999')->assertNotFound();
        $this->get('/admin/cabang/CB-999/ubah')->assertNotFound();
    }

    public function test_a_branch_can_be_updated(): void
    {
        $this->put('/admin/cabang/CB-001', $this->validPayload([
            'name' => 'Apotek Inofarma Kapten Yusuf',
            'status' => 'Tutup Sementara',
        ]))
            ->assertRedirect(route('admin.cabang.index'))
            ->assertSessionHas('success');

        $this->get('/admin/cabang/CB-001')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('branch.status', 'Tutup Sementara')
            );
    }

    public function test_coordinates_may_be_left_blank(): void
    {
        $this->post('/admin/cabang', $this->validPayload([
            'latitude' => null,
            'longitude' => null,
        ]))->assertSessionHasNoErrors();

        $this->get('/admin/cabang/CB-011')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('branch.latitude', null)
                ->where('branch.longitude', null)
            );
    }

    public function test_a_branch_with_stock_cannot_be_deleted(): void
    {
        // CB-001 was seeded with stock on every product.
        $this->delete('/admin/cabang/CB-001')
            ->assertRedirect(route('admin.cabang.index'))
            ->assertSessionHas('error');

        $this->get('/admin/cabang')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('branches', self::BRANCH_COUNT)
            );
    }

    public function test_a_branch_with_no_stock_or_orders_can_be_deleted(): void
    {
        $this->post('/admin/cabang', $this->validPayload());

        $this->delete('/admin/cabang/CB-011')
            ->assertRedirect(route('admin.cabang.index'))
            ->assertSessionHas('success');

        $this->get('/admin/cabang/CB-011')->assertNotFound();
    }

    public function test_creating_requires_valid_input(): void
    {
        $this->post('/admin/cabang', [
            'name' => '',
            'addressLine' => '',
            'kota' => '',
            'provinsi' => '',
            'latitude' => 200,
            'longitude' => 200,
            'supportsDelivery' => 'mungkin',
            'supportsPickup' => 'mungkin',
            'deliveryRadiusKm' => 0,
            'status' => 'Entah',
        ])->assertSessionHasErrors([
            'name', 'addressLine', 'kota', 'provinsi', 'latitude', 'longitude',
            'supportsDelivery', 'supportsPickup', 'deliveryRadiusKm', 'status',
        ]);
    }
}

<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `BranchCrudTest`, exercised through the Filament resource
 * at /admin instead of the legacy Inertia routes (see .ai/rules).
 */
class BranchResourceTest extends TestCase
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
            'address_line' => 'Jl. Cinere Raya No. 10',
            'kelurahan' => 'Cinere',
            'kecamatan' => 'Cinere',
            'kota' => 'Kota Depok',
            'provinsi' => 'Jawa Barat',
            'postal_code' => '16514',
            'latitude' => -6.3167,
            'longitude' => 106.7833,
            'phone' => '+62 21 7654 0001',
            'whatsapp' => '+62 812-0000-1111',
            'sia_number' => 'SIA/2026/00500',
            'apj_name' => 'Apt. Contoh Nama',
            'apj_sipa_number' => 'SIPA/2026/00500',
            'supports_delivery' => true,
            'supports_pickup' => true,
            'delivery_radius_km' => 8,
            'status' => 'aktif',
        ], $overrides);
    }

    public function test_the_list_is_seeded_with_the_ten_branches(): void
    {
        Livewire::test(ListBranches::class)
            ->assertCanSeeTableRecords(Branch::all());

        $this->assertSame(self::BRANCH_COUNT, Branch::count());
    }

    public function test_a_branch_can_be_created(): void
    {
        Livewire::test(CreateBranch::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(self::BRANCH_COUNT + 1, Branch::count());

        $branch = Branch::where('name', 'Apotek Inofarma Cinere')->firstOrFail();
        $this->assertSame('CB-011', $branch->code);
        $this->assertSame(-6.3167, (float) $branch->latitude);
        $this->assertNotNull($branch->maps_url);
        $this->assertNotEmpty($branch->operating_hours);
    }

    public function test_a_branch_can_be_updated(): void
    {
        $branch = Branch::where('code', 'CB-001')->firstOrFail();

        Livewire::test(EditBranch::class, ['record' => $branch->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Apotek Inofarma Kapten Yusuf',
                'status' => 'tutup sementara',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('tutup sementara', $branch->fresh()->status);
    }

    public function test_coordinates_may_be_left_blank(): void
    {
        Livewire::test(CreateBranch::class)
            ->fillForm($this->validPayload(['latitude' => null, 'longitude' => null]))
            ->call('create')
            ->assertHasNoFormErrors();

        $branch = Branch::where('name', 'Apotek Inofarma Cinere')->firstOrFail();
        $this->assertNull($branch->latitude);
        $this->assertNull($branch->maps_url);
    }

    public function test_a_branch_with_stock_cannot_be_deleted(): void
    {
        // CB-001 was seeded with stock on every product.
        $branch = Branch::where('code', 'CB-001')->firstOrFail();

        Livewire::test(EditBranch::class, ['record' => $branch->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($branch);
        $this->assertSame(self::BRANCH_COUNT, Branch::count());
    }

    public function test_a_branch_with_no_stock_or_orders_can_be_deleted(): void
    {
        Livewire::test(CreateBranch::class)
            ->fillForm($this->validPayload())
            ->call('create');

        $branch = Branch::where('name', 'Apotek Inofarma Cinere')->firstOrFail();

        Livewire::test(EditBranch::class, ['record' => $branch->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($branch);
    }

    public function test_creating_requires_valid_input(): void
    {
        Livewire::test(CreateBranch::class)
            ->fillForm([
                'name' => '',
                'address_line' => '',
                'kota' => '',
                'provinsi' => '',
                'latitude' => 200,
                'longitude' => 200,
                'delivery_radius_km' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'address_line' => 'required',
                'kota' => 'required',
                'provinsi' => 'required',
                'latitude' => 'max',
                'longitude' => 'max',
                'delivery_radius_km' => 'min',
            ]);
    }
}

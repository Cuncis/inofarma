<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `SupplierCrudTest`, exercised through the Filament
 * resource at /admin/beta instead of the legacy Inertia routes.
 */
class SupplierResourceTest extends TestCase
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
            'contact_person' => 'Putri Maharani',
            'email' => 'apotekcendana@mail.com',
            'phone' => '+62 361 5556 0006',
            'license_number' => 'SIA/2025/00399',
            'kota' => 'Denpasar',
            'address_line' => 'Jl. Sunset Road No. 12',
            'status' => 'aktif',
        ], $overrides);
    }

    public function test_the_list_derives_product_counts_and_revenue(): void
    {
        Livewire::test(ListSuppliers::class)
            ->assertCanSeeTableRecords(Supplier::all());

        $first = Supplier::where('name', 'Apotek Sehat Bersama')->firstOrFail();
        // 12500*1240 + 38000*860 + 24000*1580 + 29500*940
        $this->assertSame(113830000, (int) $first->products()->sum(DB::raw('price * sold_count')));
    }

    public function test_a_supplier_can_be_created(): void
    {
        Livewire::test(CreateSupplier::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(self::SUPPLIER_COUNT + 1, Supplier::count());

        $supplier = Supplier::where('name', 'Apotek Cendana')->firstOrFail();
        $this->assertSame('SEL-006', $supplier->code);
        $this->assertSame(0, $supplier->products()->count());
    }

    public function test_a_supplier_can_be_updated(): void
    {
        $supplier = Supplier::where('code', 'SEL-004')->firstOrFail();

        Livewire::test(EditSupplier::class, ['record' => $supplier->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Griya Farma',
                'email' => 'griyafarma@mail.com',
                'license_number' => 'SIA/2025/00077',
                'kota' => 'Sleman',
                'status' => 'nonaktif',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $supplier->refresh();
        $this->assertSame('Sleman', $supplier->kota);
        $this->assertSame('nonaktif', $supplier->status);
    }

    public function test_renaming_a_supplier_carries_through_to_their_products(): void
    {
        // Products hold a foreign key, so the rename needs no cascade.
        $supplier = Supplier::where('code', 'SEL-002')->firstOrFail();

        Livewire::test(EditSupplier::class, ['record' => $supplier->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Toko Obat Mandiri Jaya',
                'email' => 'obatmandiri@mail.com',
                'license_number' => 'SIA/2024/00224',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Toko Obat Mandiri Jaya', $supplier->products()->first()->fresh()->supplier->name);
    }

    public function test_a_supplier_still_stocking_products_cannot_be_deleted(): void
    {
        $supplier = Supplier::where('code', 'SEL-001')->firstOrFail();

        Livewire::test(EditSupplier::class, ['record' => $supplier->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($supplier);
    }

    public function test_a_supplier_with_no_products_can_be_deleted(): void
    {
        $supplier = Supplier::where('code', 'SEL-005')->firstOrFail();

        Livewire::test(EditSupplier::class, ['record' => $supplier->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($supplier);
    }

    public function test_creating_requires_valid_input(): void
    {
        Livewire::test(CreateSupplier::class)
            ->fillForm([
                'name' => '',
                'contact_person' => '',
                'email' => 'bukan-email',
                'phone' => 'telepon saya',
                'license_number' => '',
                'kota' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'contact_person' => 'required',
                'email' => 'email',
                'phone' => 'regex',
                'license_number' => 'required',
                'kota' => 'required',
            ]);
    }

    public function test_name_email_and_licence_must_each_be_unique(): void
    {
        Livewire::test(CreateSupplier::class)
            ->fillForm($this->validPayload(['name' => 'Farmasi Nusantara']))
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);

        Livewire::test(CreateSupplier::class)
            ->fillForm($this->validPayload(['email' => 'griyafarma@mail.com']))
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);

        Livewire::test(CreateSupplier::class)
            ->fillForm($this->validPayload(['license_number' => 'SIA/2024/00181']))
            ->call('create')
            ->assertHasFormErrors(['license_number' => 'unique']);
    }
}

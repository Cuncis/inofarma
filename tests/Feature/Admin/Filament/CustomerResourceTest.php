<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `CustomerCrudTest`, exercised through the Filament
 * resource at /admin/beta instead of the legacy Inertia routes.
 */
class CustomerResourceTest extends TestCase
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
            'name' => 'Putri Maharani',
            'email' => 'putri.maharani@mail.com',
            'phone' => '+62 819-8877-6655',
            'city' => 'Denpasar',
            'address' => 'Jl. Sunset Road No. 44',
            'status' => 'aktif',
        ], $overrides);
    }

    public function test_the_list_is_seeded_and_derives_order_stats(): void
    {
        Livewire::test(ListCustomers::class)
            ->assertCanSeeTableRecords(Customer::all());

        $this->assertSame(self::CUSTOMER_COUNT, Customer::count());
    }

    public function test_a_customer_can_be_created(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $customer = Customer::where('email', 'putri.maharani@mail.com')->firstOrFail();
        $this->assertSame('CUS-007', $customer->code);
        $this->assertSame(0, $customer->orders()->count());
        $this->assertNotNull($customer->password);

        $address = $customer->addresses()->where('is_default', true)->firstOrFail();
        $this->assertSame('Denpasar', $address->kota);
        $this->assertSame('Jl. Sunset Road No. 44', $address->address_line);
    }

    public function test_a_customer_can_be_updated(): void
    {
        $customer = Customer::where('code', 'CUS-002')->firstOrFail();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Rizky Ananda Putra',
                'email' => 'rizky.ananda@mail.com',
                'city' => 'Bekasi',
                'status' => 'nonaktif',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $customer->refresh();
        $this->assertSame('Rizky Ananda Putra', $customer->name);
        $this->assertSame('nonaktif', $customer->status);

        $address = $customer->addresses()->where('is_default', true)->firstOrFail();
        $this->assertSame('Bekasi', $address->kota);
    }

    public function test_editing_prefills_the_default_address(): void
    {
        $customer = Customer::where('code', 'CUS-001')->firstOrFail();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->assertFormSet([
                'city' => $customer->addresses()->where('is_default', true)->first()?->kota,
            ]);
    }

    public function test_a_customer_with_orders_cannot_be_deleted(): void
    {
        $customer = Customer::where('code', 'CUS-001')->firstOrFail();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($customer);
    }

    public function test_a_customer_without_orders_can_be_deleted(): void
    {
        $customer = Customer::where('code', 'CUS-006')->firstOrFail();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($customer);
    }

    public function test_creating_requires_valid_input(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => '',
                'email' => 'bukan-email',
                'phone' => 'nomor saya',
                'city' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'email',
                'phone' => 'regex',
                'city' => 'required',
            ]);
    }

    public function test_an_email_cannot_be_taken_twice(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm($this->validPayload(['email' => 'kirana.wijaya@mail.com']))
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_a_customer_keeps_their_own_email_when_edited(): void
    {
        $customer = Customer::where('code', 'CUS-001')->firstOrFail();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Kirana Wijaya',
                'email' => 'kirana.wijaya@mail.com',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_changing_the_email_keeps_the_order_history(): void
    {
        $customer = Customer::where('code', 'CUS-001')->firstOrFail();
        $ordersBefore = $customer->orders()->count();

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Kirana Wijaya',
                'email' => 'kirana.baru@mail.com',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $customer->refresh();
        $this->assertSame('kirana.baru@mail.com', $customer->email);
        $this->assertSame($ordersBefore, $customer->orders()->count());
    }
}

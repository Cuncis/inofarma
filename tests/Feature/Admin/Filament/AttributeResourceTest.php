<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Attributes\Pages\CreateAttribute;
use App\Filament\Resources\Attributes\Pages\EditAttribute;
use App\Filament\Resources\Attributes\Pages\ListAttributes;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Phase 1 pilot for the Filament migration (see .ai/rules) — same behaviour
 * as `AttributeCrudTest`, exercised through the Filament resource at
 * /admin/beta instead of the legacy Inertia routes.
 */
class AttributeResourceTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_list_shows_every_attribute(): void
    {
        Attribute::factory()->create(['name' => 'Bentuk Sediaan', 'slug' => 'bentuk-sediaan', 'values' => ['Tablet', 'Kapsul', 'Sirup']]);

        Livewire::test(ListAttributes::class)
            ->assertCanSeeTableRecords(Attribute::all());
    }

    public function test_a_pilihan_attribute_can_be_created(): void
    {
        Livewire::test(CreateAttribute::class)
            ->fillForm([
                'name' => 'Kemasan',
                'type' => 'pilihan',
                'values' => ['Strip', 'Botol', 'Box'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'name' => 'Kemasan',
            'slug' => 'kemasan',
        ]);
        $this->assertSame(['Strip', 'Botol', 'Box'], Attribute::where('slug', 'kemasan')->value('values'));
    }

    public function test_a_teks_attribute_needs_no_values(): void
    {
        Livewire::test(CreateAttribute::class)
            ->fillForm([
                'name' => 'Volume',
                'type' => 'teks',
                'values' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(Attribute::where('slug', 'volume')->value('values'));
    }

    public function test_a_pilihan_attribute_requires_at_least_one_value(): void
    {
        Livewire::test(CreateAttribute::class)
            ->fillForm([
                'name' => 'Rasa',
                'type' => 'pilihan',
                'values' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['values' => 'required']);
    }

    public function test_the_name_must_be_unique(): void
    {
        Attribute::factory()->create(['name' => 'Bentuk Sediaan', 'slug' => 'bentuk-sediaan']);

        Livewire::test(CreateAttribute::class)
            ->fillForm([
                'name' => 'Bentuk Sediaan',
                'type' => 'pilihan',
                'values' => ['A'],
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_an_attribute_can_be_updated(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Bentuk Sediaan', 'slug' => 'bentuk-sediaan', 'values' => ['Tablet']]);

        Livewire::test(EditAttribute::class, ['record' => $attribute->getKey()])
            ->fillForm([
                'name' => 'Bentuk Sediaan',
                'type' => 'pilihan',
                'values' => ['Tablet', 'Kapsul', 'Sirup', 'Salep'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Tablet', 'Kapsul', 'Sirup', 'Salep'], $attribute->fresh()->values);
    }

    public function test_an_attribute_can_be_deleted(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Bentuk Sediaan', 'slug' => 'bentuk-sediaan']);

        Livewire::test(EditAttribute::class, ['record' => $attribute->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($attribute);
    }
}

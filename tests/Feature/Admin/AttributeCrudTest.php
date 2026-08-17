<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class AttributeCrudTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();

        Attribute::factory()->create(['name' => 'Bentuk Sediaan', 'slug' => 'bentuk-sediaan', 'values' => ['Tablet', 'Kapsul', 'Sirup']]);
    }

    public function test_the_list_shows_every_attribute(): void
    {
        $this->get('/admin/atribut')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/AttributeList')
                ->has('attributes', 1)
                ->where('attributes.0.name', 'Bentuk Sediaan')
                ->where('attributes.0.values', ['Tablet', 'Kapsul', 'Sirup'])
            );
    }

    public function test_a_pilihan_attribute_can_be_created(): void
    {
        $this->post('/admin/atribut', [
            'name' => 'Kemasan',
            'type' => 'Pilihan',
            'values' => ['Strip', 'Botol', 'Box'],
        ])
            ->assertRedirect(route('admin.atribut.index'))
            ->assertSessionHas('success');

        $this->get('/admin/atribut')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('attributes', 2)
                ->where('attributes.1.slug', 'kemasan')
                ->where('attributes.1.values', ['Strip', 'Botol', 'Box'])
            );
    }

    public function test_a_teks_attribute_needs_no_values(): void
    {
        $this->post('/admin/atribut', [
            'name' => 'Volume',
            'type' => 'Teks',
            'values' => [],
        ])->assertSessionHasNoErrors();

        $this->assertSame([], Attribute::where('slug', 'volume')->value('values') ?? []);
    }

    public function test_a_pilihan_attribute_requires_at_least_one_value(): void
    {
        $this->post('/admin/atribut', [
            'name' => 'Rasa',
            'type' => 'Pilihan',
            'values' => [],
        ])->assertSessionHasErrors('values');
    }

    public function test_the_name_must_be_unique(): void
    {
        $this->post('/admin/atribut', [
            'name' => 'Bentuk Sediaan',
            'type' => 'Pilihan',
            'values' => ['A'],
        ])->assertSessionHasErrors(['name' => 'Atribut dengan nama ini sudah ada.']);
    }

    public function test_an_attribute_can_be_updated(): void
    {
        $this->put('/admin/atribut/bentuk-sediaan', [
            'name' => 'Bentuk Sediaan',
            'type' => 'Pilihan',
            'values' => ['Tablet', 'Kapsul', 'Sirup', 'Salep'],
        ])
            ->assertRedirect(route('admin.atribut.index'))
            ->assertSessionHas('success');

        $this->get('/admin/atribut')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('attributes.0.values', ['Tablet', 'Kapsul', 'Sirup', 'Salep'])
            );
    }

    public function test_an_attribute_can_be_deleted(): void
    {
        $this->delete('/admin/atribut/bentuk-sediaan')
            ->assertRedirect(route('admin.atribut.index'))
            ->assertSessionHas('success');

        $this->get('/admin/atribut')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('attributes', 0));
    }

    public function test_an_unknown_attribute_is_a_404(): void
    {
        $this->get('/admin/atribut/tidak-ada/ubah')->assertNotFound();
    }
}

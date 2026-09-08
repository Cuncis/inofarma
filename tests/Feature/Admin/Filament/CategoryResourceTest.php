<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `CategoryCrudTest`, exercised through the Filament
 * resource at /admin instead of the legacy Inertia routes.
 */
class CategoryResourceTest extends TestCase
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
            'name' => 'Perawatan Bayi',
            'slug' => 'perawatan-bayi',
            'status' => 'aktif',
            'description' => 'Produk kebersihan dan perawatan khusus bayi.',
        ], $overrides);
    }

    public function test_the_list_is_seeded_and_carries_product_counts(): void
    {
        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords(Category::all());

        $kesehatan = Category::where('name', 'Kesehatan')->firstOrFail();
        $this->assertSame(1, $kesehatan->products()->count());
        $this->assertSame(self::CATEGORY_COUNT, Category::count());
    }

    public function test_a_category_can_be_created(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::where('name', 'Perawatan Bayi')->firstOrFail();
        $this->assertSame('perawatan-bayi', $category->slug);
        $this->assertSame(0, $category->products()->count());
    }

    public function test_a_slug_is_generated_when_left_blank(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm($this->validPayload(['name' => 'Nutrisi Lansia', 'slug' => '']))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Nutrisi Lansia', 'slug' => 'nutrisi-lansia']);
    }

    public function test_a_duplicate_slug_gets_a_suffix_instead_of_colliding(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm($this->validPayload(['name' => 'Vitamin A', 'slug' => 'vitamin-suplemen']))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Vitamin A', 'slug' => 'vitamin-suplemen-2']);
    }

    public function test_an_unknown_category_is_a_404(): void
    {
        $this->get('/admin/kategori/999999/edit')->assertNotFound();
    }

    public function test_a_category_can_be_updated(): void
    {
        $category = Category::where('slug', 'kebutuhan-keluarga')->firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Kebutuhan Keluarga & Rumah Tangga',
                'slug' => 'kebutuhan-keluarga',
                'status' => 'nonaktif',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertSame('Kebutuhan Keluarga & Rumah Tangga', $category->name);
        $this->assertSame('nonaktif', $category->status);
    }

    public function test_renaming_a_category_carries_through_to_its_products(): void
    {
        $category = Category::where('slug', 'kebutuhan-keluarga')->firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Kebutuhan Keluarga & Rumah Tangga',
                'slug' => 'kebutuhan-keluarga',
            ]))
            ->call('save');

        $product = $category->products()->firstOrFail();
        $this->assertSame('Kebutuhan Keluarga & Rumah Tangga', $product->category->name);
    }

    public function test_a_category_still_holding_products_cannot_be_deleted(): void
    {
        $category = Category::where('slug', 'vitamin-suplemen')->firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->callAction('delete');

        $this->assertNotSoftDeleted($category);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm($this->validPayload())
            ->call('create');

        $category = Category::where('name', 'Perawatan Bayi')->firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($category);
    }

    public function test_creating_requires_valid_input(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm(['name' => '', 'slug' => 'Huruf Besar Dan Spasi', 'status' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'regex', 'status' => 'required']);
    }

    public function test_a_category_name_cannot_be_duplicated(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm($this->validPayload(['name' => 'Vitamin & Suplemen', 'slug' => 'suplemen-baru']))
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_a_category_keeps_its_own_name_when_edited(): void
    {
        $category = Category::where('slug', 'vitamin-suplemen')->firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Vitamin & Suplemen',
                'slug' => 'vitamin-suplemen',
                'description' => 'Deskripsi baru.',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();
    }
}

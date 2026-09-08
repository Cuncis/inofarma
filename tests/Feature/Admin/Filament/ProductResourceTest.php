<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `ProductCrudTest` and `ProductImageTest`, exercised
 * through the Filament resource at /admin instead of the legacy
 * Inertia routes.
 */
class ProductResourceTest extends TestCase
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
        $category = Category::where('name', 'Obat Bebas')->firstOrFail();
        $seller = Supplier::where('name', 'Apotek Sehat Bersama')->firstOrFail();

        return array_merge([
            'name' => 'Ibuprofen 400mg',
            'category_id' => $category->id,
            'supplier_id' => $seller->id,
            'unit' => 'Strip',
            'status' => 'aktif',
            'price' => 17500,
            'old_price' => null,
            'requires_prescription' => false,
            'blurb' => 'Meredakan nyeri dan peradangan ringan.',
        ], $overrides);
    }

    public function test_the_list_is_seeded_from_the_catalogue(): void
    {
        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords(Product::orderBy('id')->limit(10)->get());

        $this->assertSame(self::PRODUCT_COUNT, Product::count());
    }

    public function test_the_listed_stock_is_the_sum_across_every_branch(): void
    {
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(ListProducts::class)
            ->assertTableColumnStateSet('stocks_sum_quantity', 482, record: $product);
    }

    public function test_a_product_can_be_created_and_appears_in_the_list(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->validPayload())
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Ibuprofen 400mg')->firstOrFail();
        $this->assertSame('PRD-013', $product->sku);
        $this->assertSame(17500, $product->price);
        $this->assertSame(0, (int) $product->stocks()->sum('quantity'));
    }

    public function test_a_product_can_be_viewed(): void
    {
        $product = Product::where('sku', 'PRD-003')->firstOrFail();

        Livewire::test(ViewProduct::class, ['record' => $product->getKey()])
            ->assertSee('Vitamin C 1000mg');
    }

    public function test_an_unknown_product_is_a_404(): void
    {
        $this->get('/admin/produk/999999/edit')->assertNotFound();
    }

    public function test_a_product_can_be_updated(): void
    {
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm($this->validPayload([
                'name' => 'Paracetamol 650mg',
                'price' => 14000,
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertSame('Paracetamol 650mg', $product->name);
        $this->assertSame(14000, $product->price);
        // Editing the product does not disturb what is on the shelves.
        $this->assertSame(482, (int) $product->stocks()->sum('quantity'));
    }

    public function test_a_product_can_be_deleted(): void
    {
        $product = Product::where('sku', 'PRD-002')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($product);
    }

    public function test_deleting_a_product_leaves_past_orders_intact(): void
    {
        $product = Product::where('sku', 'PRD-002')->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('delete');

        $order = Order::where('number', 'INO-2446')->firstOrFail();
        $item = $order->items()->firstOrFail();
        $this->assertSame('Amoxicillin 500mg', $item->product_name);
        $this->assertSame(38000, $item->unit_price);
    }

    public function test_creating_requires_valid_input(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => '',
                'category_id' => null,
                'supplier_id' => null,
                'unit' => null,
                'status' => null,
                'price' => -5,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'category_id', 'supplier_id', 'unit', 'status', 'price']);
    }

    public function test_the_struck_through_price_must_beat_the_selling_price(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->validPayload(['price' => 20000, 'old_price' => 15000]))
            ->call('create')
            ->assertHasFormErrors(['old_price' => 'gt']);
    }

    public function test_a_product_can_be_created_with_full_pharmacy_data(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->validPayload([
                'name' => 'Loratadine 10mg',
                'drug_class' => 'bebas',
                'nie_bpom' => 'DKL1234567890A1',
                'composition' => 'Loratadine 10 mg per tablet.',
                'manufacturer' => 'PT Kalbe Farma Tbk',
                'max_qty_per_order' => 3,
                'storage' => 'suhu ruang',
                'weight_grams' => 20,
                'length_cm' => 5,
                'width_cm' => 5,
                'height_cm' => 10,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Loratadine 10mg')->firstOrFail();
        $this->assertSame('bebas', $product->drug_class);
        $this->assertSame('DKL1234567890A1', $product->nie_bpom);
        $this->assertSame(3, $product->max_qty_per_order);
        $this->assertSame(20, $product->weight_grams);
        $this->assertSame(5, $product->length_cm);
        $this->assertSame(5, $product->width_cm);
        $this->assertSame(10, $product->height_cm);
    }

    public function test_a_product_without_pharmacy_data_gets_safe_defaults(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->validPayload(['name' => 'Kapas Bulat 50g']))
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Kapas Bulat 50g')->firstOrFail();
        $this->assertSame('non-obat', $product->drug_class);
        $this->assertSame('suhu ruang', $product->storage);
        $this->assertFalse($product->needs_warning_label);
    }

    public function test_bebas_terbatas_requires_a_warning(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->validPayload(['name' => 'Obat Flu Malam', 'drug_class' => 'bebas terbatas']))
            ->call('create')
            ->assertHasFormErrors(['warning' => 'required']);
    }

    public function test_the_seeded_cough_syrup_carries_its_p1_warning(): void
    {
        $product = Product::where('sku', 'PRD-010')->firstOrFail();

        $this->assertSame('bebas terbatas', $product->drug_class);
        $this->assertTrue($product->needs_warning_label);
        $this->assertSame('Awas! Obat Keras. Bacalah aturan pemakaiannya.', $product->warning);
    }

    // --- Images (ImagesRelationManager) ---

    private function imagesFor(Product $product)
    {
        return $product->images()->orderBy('position')->get();
    }

    public function test_an_uploaded_image_is_added_alongside_the_seeded_one(): void
    {
        Storage::fake('public');
        $product = Product::where('sku', 'PRD-011')->firstOrFail();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('upload', data: ['images' => [UploadedFile::fake()->image('produk.jpg', 800, 800)]]);

        $images = $this->imagesFor($product);
        $this->assertCount(2, $images);
        $this->assertTrue($images->first()->is_primary);
        $this->assertFalse($images->last()->is_primary);
    }

    public function test_uploading_resizes_into_an_original_and_a_thumbnail(): void
    {
        Storage::fake('public');
        $product = Product::where('sku', 'PRD-011')->firstOrFail();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('upload', data: ['images' => [UploadedFile::fake()->image('produk.jpg', 2000, 2000)]]);

        $uploaded = $this->imagesFor($product)->last();
        $relative = ltrim(parse_url($uploaded->path, PHP_URL_PATH), '/');
        $relative = preg_replace('#^storage/#', '', $relative);

        Storage::disk('public')->assertExists($relative);
        Storage::disk('public')->assertExists(preg_replace('/(\.\w+)$/', '-thumb$1', $relative));
    }

    public function test_a_different_image_can_be_made_primary(): void
    {
        Storage::fake('public');
        $product = Product::where('sku', 'PRD-011')->firstOrFail();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('upload', data: ['images' => [UploadedFile::fake()->image('satu.jpg')]]);

        $second = $this->imagesFor($product)->last();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('makePrimary', $second);

        $this->assertTrue($second->refresh()->is_primary);
        $this->assertSame(1, ProductImage::where('product_id', $second->product_id)->where('is_primary', true)->count());
    }

    public function test_deleting_the_primary_image_promotes_the_next_one(): void
    {
        Storage::fake('public');
        $product = Product::where('sku', 'PRD-011')->firstOrFail();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('upload', data: ['images' => [UploadedFile::fake()->image('satu.jpg')]]);

        $images = $this->imagesFor($product);
        $first = $images->first();
        $second = $images->last();

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('delete', $first);

        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_a_seeded_static_image_is_never_sent_to_the_uploader_disk_on_delete(): void
    {
        Storage::fake('public');
        $product = Product::where('sku', 'PRD-001')->firstOrFail();
        $seeded = $this->imagesFor($product)->first();
        $this->assertStringStartsWith('/media/', $seeded->path);

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->loadTable()
            ->callTableAction('delete', $seeded);

        $this->assertModelMissing($seeded);
    }
}

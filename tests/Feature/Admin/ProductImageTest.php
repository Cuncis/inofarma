<?php

namespace Tests\Feature\Admin;

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed();
        $this->signInAsAdmin();
    }

    private function imagesFor(string $sku): Collection
    {
        return ProductImage::whereHas('product', fn ($q) => $q->where('sku', $sku))
            ->orderBy('position')
            ->get();
    }

    public function test_an_uploaded_image_is_added_alongside_the_seeded_one(): void
    {
        // Every seeded product already carries one static image (CatalogSeeder).
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->image('produk.jpg', 800, 800)],
        ])->assertSessionHas('success');

        $this->get('/admin/produk/PRD-011')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('product.images', 2)
                ->where('product.images.0.isPrimary', true)
                ->where('product.images.1.isPrimary', false)
            );
    }

    public function test_uploading_resizes_into_an_original_and_a_thumbnail(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->image('produk.jpg', 2000, 2000)],
        ]);

        $uploaded = $this->imagesFor('PRD-011')->last();
        $relative = ltrim(parse_url($uploaded->path, PHP_URL_PATH), '/');
        $relative = preg_replace('#^storage/#', '', $relative);

        Storage::disk('public')->assertExists($relative);
        Storage::disk('public')->assertExists(preg_replace('/(\.\w+)$/', '-thumb$1', $relative));
    }

    public function test_several_images_can_be_uploaded_at_once(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.jpg'),
            ],
        ]);

        $this->assertCount(3, $this->imagesFor('PRD-011'));
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->create('berkas.pdf', 100)],
        ])->assertSessionHasErrors('images.0');
    }

    public function test_images_can_be_reordered(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->image('satu.jpg')],
        ]);

        $ids = $this->imagesFor('PRD-011')->pluck('id');

        $this->post('/admin/produk/PRD-011/gambar/urutkan', [
            'order' => $ids->reverse()->values()->all(),
        ])->assertSessionHas('success');

        $this->get('/admin/produk/PRD-011')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('product.images.0.id', $ids->last())
            );
    }

    public function test_a_different_image_can_be_made_primary(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->image('satu.jpg')],
        ]);

        $second = $this->imagesFor('PRD-011')->last();

        $this->post("/admin/produk/PRD-011/gambar/{$second->id}/utama")
            ->assertSessionHas('success');

        $this->assertTrue($second->refresh()->is_primary);
        $this->assertSame(1, ProductImage::where('product_id', $second->product_id)->where('is_primary', true)->count());
    }

    public function test_deleting_the_primary_image_promotes_the_next_one(): void
    {
        $this->post('/admin/produk/PRD-011/gambar', [
            'images' => [UploadedFile::fake()->image('satu.jpg')],
        ]);

        $images = $this->imagesFor('PRD-011');
        $first = $images->first();
        $second = $images->last();

        $this->delete("/admin/produk/PRD-011/gambar/{$first->id}")
            ->assertSessionHas('success');

        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_a_seeded_static_image_is_never_sent_to_the_uploader_disk_on_delete(): void
    {
        $seeded = $this->imagesFor('PRD-001')->first();
        $this->assertStringStartsWith('/media/', $seeded->path);

        $this->delete("/admin/produk/PRD-001/gambar/{$seeded->id}")
            ->assertSessionHas('success');

        $this->assertModelMissing($seeded);
    }
}

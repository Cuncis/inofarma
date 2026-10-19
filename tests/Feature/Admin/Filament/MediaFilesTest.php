<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\MediaFiles;
use App\Models\Role;
use App\Models\User;
use App\Support\MediaLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class MediaFilesTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        MediaLibrary::forget();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function addFile(string $path): void
    {
        Storage::disk('public')->put($path, 'x');
        MediaLibrary::forget();
    }

    public function test_it_lists_uploads_product_photos_and_site_assets_with_full_links(): void
    {
        $this->addFile('media/banner-abc12.png');
        $this->addFile('products/5/foto.jpg');

        $items = MediaLibrary::items();

        $upload = $items->firstWhere('path', 'media/banner-abc12.png');
        $this->assertSame('unggahan', $upload['source']);
        $this->assertTrue($upload['deletable']);
        $this->assertStringStartsWith('http', $upload['url']);
        $this->assertStringEndsWith('/storage/media/banner-abc12.png', $upload['url']);

        $product = $items->firstWhere('path', 'products/5/foto.jpg');
        $this->assertSame('produk', $product['source']);
        $this->assertFalse($product['deletable']);

        $this->assertTrue($items->contains(fn (array $item) => $item['source'] === 'situs'));
        $this->assertStringStartsWith('http', $items->firstWhere('source', 'situs')['url']);
    }

    public function test_thumbnails_and_non_media_files_are_left_out(): void
    {
        $this->addFile('products/5/foto.jpg');
        $this->addFile('products/5/foto-thumb.jpg');
        $this->addFile('media/catatan.txt');

        $paths = MediaLibrary::items()->pluck('path');

        $this->assertTrue($paths->contains('products/5/foto.jpg'));
        $this->assertFalse($paths->contains('products/5/foto-thumb.jpg'));
        $this->assertFalse($paths->contains('media/catatan.txt'));
    }

    public function test_the_page_searches_and_filters_by_source(): void
    {
        $this->addFile('media/promo-lebaran-aaaaa.png');
        $this->addFile('products/9/obat.jpg');

        Livewire::test(MediaFiles::class)
            ->assertSee('promo-lebaran-aaaaa.png')
            ->assertSee('obat.jpg')
            ->set('source', 'produk')
            ->assertSee('obat.jpg')
            ->assertDontSee('promo-lebaran-aaaaa.png')
            ->set('source', '')
            ->set('search', 'lebaran')
            ->assertSee('promo-lebaran-aaaaa.png')
            ->assertDontSee('obat.jpg');
    }

    public function test_the_source_tabs_count_the_files_matching_the_search(): void
    {
        $this->addFile('media/zqx-a-ggggg.png');
        $this->addFile('media/zqx-b-ggggg.png');
        $this->addFile('products/9/zqx-c.jpg');

        $counts = Livewire::test(MediaFiles::class)
            ->set('search', 'zqx')
            ->set('source', 'produk')
            ->instance()
            ->sourceCounts();

        $this->assertSame(3, $counts['']);
        $this->assertSame(2, $counts['unggahan']);
        $this->assertSame(1, $counts['produk']);
        $this->assertArrayNotHasKey('situs', $counts);
    }

    public function test_an_uploaded_file_can_be_deleted_but_a_product_photo_cannot(): void
    {
        $this->addFile('media/hapus-saya-bbbbb.png');
        $this->addFile('products/9/obat.jpg');

        Livewire::test(MediaFiles::class)
            ->call('delete', 'media/hapus-saya-bbbbb.png')
            ->call('delete', 'products/9/obat.jpg')
            ->call('delete', 'media/../products/9/obat.jpg');

        Storage::disk('public')->assertMissing('media/hapus-saya-bbbbb.png');
        Storage::disk('public')->assertExists('products/9/obat.jpg');
    }

    public function test_files_can_be_uploaded_with_a_readable_name(): void
    {
        Livewire::test(MediaFiles::class)
            ->callAction('upload', ['files' => [UploadedFile::fake()->image('Banner Promo.png')]])
            ->assertHasNoActionErrors();

        $stored = collect(Storage::disk('public')->allFiles('media'));

        $this->assertCount(1, $stored);
        $this->assertMatchesRegularExpression('#^media/banner-promo-[a-z0-9]{5}\.png$#', $stored->first());
        $this->assertTrue(MediaLibrary::items()->contains('path', $stored->first()));
    }

    public function test_files_dropped_on_the_page_are_uploaded_with_a_readable_name(): void
    {
        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->image('Promo Akhir Tahun.jpg'), UploadedFile::fake()->create('Brosur.pdf', 100, 'application/pdf')])
            ->call('saveDropped')
            ->assertNotified(__(':count file diunggah.', ['count' => 2]))
            ->assertSet('dropped', []);

        $stored = collect(Storage::disk('public')->allFiles('media'))->sort()->values();

        $this->assertCount(2, $stored);
        $this->assertMatchesRegularExpression('#^media/brosur-[a-z0-9]{5}\.pdf$#', $stored[0]);
        $this->assertMatchesRegularExpression('#^media/promo-akhir-tahun-[a-z0-9]{5}\.jpg$#', $stored[1]);
        $this->assertTrue(MediaLibrary::items()->contains('path', $stored[1]));
    }

    public function test_dropped_files_of_the_wrong_type_or_size_are_refused(): void
    {
        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->create('catatan.txt', 1, 'text/plain')])
            ->call('saveDropped')
            ->assertNotified(__('Upload gagal'))
            ->assertSet('dropped', []);

        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->image('raksasa.png')->size(MediaFiles::MAX_SIZE + 1)])
            ->call('saveDropped')
            ->assertNotified(__('Upload gagal'));

        $this->assertSame([], Storage::disk('public')->allFiles('media'));
    }

    public function test_a_viewer_sees_the_files_but_not_upload_or_delete(): void
    {
        $this->post('/admin/keluar');
        Role::findOrCreate('Pelihat Media', 'web')->syncPermissions(['Media:Lihat']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $user->assignRole('Pelihat Media');
        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);

        $this->addFile('media/lihat-saja-ccccc.png');

        Livewire::test(MediaFiles::class)
            ->assertSee('lihat-saja-ccccc.png')
            ->assertActionHidden('upload')
            ->call('delete', 'media/lihat-saja-ccccc.png')
            ->assertForbidden();

        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->image('Diam Diam.png')])
            ->call('saveDropped')
            ->assertForbidden();

        Storage::disk('public')->assertExists('media/lihat-saja-ccccc.png');
        $this->assertCount(1, Storage::disk('public')->allFiles('media'));
    }

    public function test_someone_without_the_view_permission_cannot_open_it(): void
    {
        $this->post('/admin/keluar');
        Role::findOrCreate('Tanpa Media', 'web')->syncPermissions(['Produk:Lihat']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $user->assignRole('Tanpa Media');
        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);

        $this->get('/admin/media-file')->assertForbidden();
    }

    public function test_the_list_survives_a_cache_that_only_unserializes_plain_data(): void
    {
        config(['cache.serializable_classes' => false, 'cache.default' => 'file']);
        $this->addFile('media/dari-cache-ddddd.png');

        MediaLibrary::items();

        $this->assertTrue(MediaLibrary::items()->contains('path', 'media/dari-cache-ddddd.png'));
    }

    public function test_a_malformed_cache_entry_is_ignored_and_rebuilt(): void
    {
        $this->addFile('media/segar-eeeee.png');
        cache()->put('media-library.items.v2', ['bukan', 'daftar', 'file'], 60);

        $this->assertTrue(MediaLibrary::items()->contains('path', 'media/segar-eeeee.png'));
        Livewire::test(MediaFiles::class)->assertSee('segar-eeeee.png');
    }

    public function test_the_display_option_changes_the_layout_and_page_size(): void
    {
        foreach (range(1, 14) as $n) {
            $this->addFile('media/gambar-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT).'-fffff.png');
        }

        $pages = fn (string $display) => Livewire::test(MediaFiles::class)
            ->set('search', 'fffff')
            ->set('display', $display)
            ->instance()
            ->pageCount();

        $this->assertSame(1, $pages('sedang'));
        $this->assertSame(1, $pages('kecil'));
        $this->assertSame(1, $pages('daftar'));
        $this->assertSame(2, $pages('besar'));

        Livewire::test(MediaFiles::class)->assertSet('display', 'sedang')->assertSee('Salin tautan');

        Livewire::test(MediaFiles::class)
            ->set('search', 'fffff')
            ->set('display', 'daftar')
            ->assertSee('/storage/media/gambar-01-fffff.png');

        // An unknown option falls back to the default instead of breaking the page.
        Livewire::test(MediaFiles::class)->set('search', 'fffff')->set('display', 'aneh')->assertSee('gambar-01-fffff.png');
    }
}

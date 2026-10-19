<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\EditHomePage;
use App\Filament\Pages\WebsiteContent;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\HomePageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class WebsiteContentTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed();
        $this->signInAsAdmin();
    }

    private function signInWith(array $permissions): void
    {
        $this->post('/admin/keluar');
        Role::findOrCreate('Uji Konten', 'web')->syncPermissions($permissions);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $user->assignRole('Uji Konten');
        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);
    }

    public function test_the_home_page_shows_the_shipped_content_until_something_is_saved(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/Home')
            ->where('content.hero.0.image', '/media/images/hero/coming-soon.webp')
            ->has('content.hero', 4)
            ->has('content.promo', 2)
            ->has('content.bottom', 3)
            ->has('content.brands', 9)
            ->has('content.benefits', 9)
            ->has('content.testimonials', 4)
            ->where('content.allProductsIcon', '/media/images/categories/semua-produk.png')
            ->where('content.texts.1.paragraphs.1', '*S&K berlaku')
            ->where('content.contacts.0.icon', 'phone')
            ->where('content.contacts.0.lines.1', 'WhatsApp: 0812-1401-8964'));
    }

    public function test_the_content_menu_lists_the_home_page(): void
    {
        Livewire::test(WebsiteContent::class)
            ->assertOk()
            ->assertSee(__('Beranda'))
            ->assertSee(EditHomePage::getUrl());
    }

    public function test_the_editor_opens_filled_with_the_current_content(): void
    {
        $category = Category::orderBy('position')->orderBy('id')->firstOrFail();

        Livewire::test(EditHomePage::class)
            ->assertOk()
            ->assertSchemaStateSet([
                'contactTitle' => 'Hubungi Inofarma Kapan Saja',
                "categoryIcons.category{$category->id}" => $category->image_path,
            ], 'form');
    }

    public function test_texts_and_slides_can_be_changed_and_reordered(): void
    {
        $component = Livewire::test(EditHomePage::class);
        $hero = array_values($component->get('data.hero'));

        $component
            ->set('data.hero', [array_merge($hero[1], ['alt' => 'Promo Lebaran']), $hero[0]])
            ->set('data.promo', [])
            ->set('data.contactTitle', 'Hubungi Kami')
            ->set('data.texts', [['title' => 'Tentang Inofarma', 'body' => "Paragraf satu.\n\nParagraf dua."]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotified(__('Konten Beranda disimpan.'));

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('content.hero', 2)
            ->where('content.hero.0.alt', 'Promo Lebaran')
            ->where('content.hero.0.image', '/media/images/hero/hero-banner-1.jpg')
            ->where('content.hero.1.image', '/media/images/hero/coming-soon.webp')
            ->has('content.promo', 0)
            ->has('content.bottom', 3)
            ->where('content.contactTitle', 'Hubungi Kami')
            ->where('content.texts.0.paragraphs', ['Paragraf satu.', 'Paragraf dua.']));

        $this->assertTrue(AuditLog::where('action', 'konten_diubah')->exists());
    }

    public function test_an_uploaded_slide_image_is_stored_and_shown_by_its_url(): void
    {
        $component = Livewire::test(EditHomePage::class);
        $firstKey = array_key_first($component->get('data.hero'));

        $component
            ->set("data.hero.{$firstKey}.image", [UploadedFile::fake()->image('banner-baru.jpg', 1600, 900)])
            ->call('save')
            ->assertHasNoErrors();

        $stored = Storage::disk('public')->allFiles(HomePageContent::UPLOAD_DIRECTORY);
        $this->assertCount(1, $stored);

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content.hero.0.image', HomePageContent::imageUrl($stored[0])));
    }

    public function test_a_category_icon_and_the_all_products_icon_can_be_replaced(): void
    {
        $category = Category::orderBy('position')->orderBy('id')->firstOrFail();
        $untouched = Category::whereKeyNot($category->id)->firstOrFail();
        $untouchedIcon = $untouched->image_path;

        Livewire::test(EditHomePage::class)
            ->set("data.categoryIcons.category{$category->id}", [UploadedFile::fake()->image('ikon.png', 200, 200)])
            ->set('data.allProductsIcon', [UploadedFile::fake()->image('semua.png', 200, 200)])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(2, Storage::disk('public')->allFiles(HomePageContent::UPLOAD_DIRECTORY));
        $this->assertStringContainsString('/storage/'.HomePageContent::UPLOAD_DIRECTORY.'/', $category->refresh()->image_path);
        $this->assertSame($untouchedIcon, $untouched->refresh()->image_path);

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content.allProductsIcon', fn (string $url) => str_contains($url, '/storage/'.HomePageContent::UPLOAD_DIRECTORY.'/'))
            ->where('catalog.categories', fn ($categories) => collect($categories)->firstWhere('name', $category->name)['image'] === $category->image_path));
    }

    public function test_a_slide_needs_an_image_alt_text_and_a_shop_path_or_full_link(): void
    {
        $component = Livewire::test(EditHomePage::class);
        $firstKey = array_key_first($component->get('data.hero'));

        $component
            ->set("data.hero.{$firstKey}.href", 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(["data.hero.{$firstKey}.href"]);

        $component
            ->set("data.hero.{$firstKey}.href", 'https://info.inofarma.com/')
            ->set("data.hero.{$firstKey}.alt", '')
            ->call('save')
            ->assertHasErrors(["data.hero.{$firstKey}.alt"]);

        $component
            ->set('data.hero', [])
            ->call('save')
            ->assertHasErrors(['data.hero']);

        $this->assertNull(Setting::where('key', HomePageContent::KEY)->first());
    }

    public function test_a_non_image_upload_is_refused(): void
    {
        Livewire::test(EditHomePage::class)
            ->set('data.allProductsIcon', [UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['data.allProductsIcon']);

        $this->assertSame([], Storage::disk('public')->allFiles(HomePageContent::UPLOAD_DIRECTORY));
    }

    public function test_a_viewer_sees_the_content_but_cannot_save(): void
    {
        $this->signInWith(['Konten:Lihat']);

        Livewire::test(EditHomePage::class)
            ->assertOk()
            ->assertDontSee(__('Simpan'))
            ->call('save')
            ->assertForbidden();
    }

    public function test_someone_without_the_content_permission_cannot_open_either_page(): void
    {
        $this->signInWith(['Produk:Lihat']);

        $this->get('/admin/konten-website')->assertForbidden();
        $this->get('/admin/konten-website/beranda')->assertForbidden();
    }

    public function test_section_headings_can_be_changed(): void
    {
        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content.headings.recommended', 'Rekomendasi Untukmu')
            ->where('content.headings.testimonials', 'Testimoni Sobat Ino'));

        Livewire::test(EditHomePage::class)
            ->assertSchemaStateSet(['headings.brands' => 'Brand Terlaris'], 'form')
            ->set('data.headings.recommended', 'Pilihan Apoteker')
            ->set('data.headings.nearest', 'Cabang Terdekat')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content.headings.recommended', 'Pilihan Apoteker')
            ->where('content.headings.nearest', 'Cabang Terdekat')
            ->where('content.headings.trending', 'Produk Terlaris Kami'));
    }

    public function test_a_heading_cannot_be_left_empty(): void
    {
        Livewire::test(EditHomePage::class)
            ->set('data.headings.benefits', '')
            ->call('save')
            ->assertHasErrors(['data.headings.benefits']);
    }

    public function test_content_saved_before_headings_existed_still_gets_every_heading(): void
    {
        Setting::create(['branch_id' => null, 'key' => HomePageContent::KEY, 'type' => 'json', 'value' => json_encode([
            'contactTitle' => 'Hubungi Kami',
            'headings' => ['brands' => 'Merek Pilihan', 'unknown' => 'x'],
        ])]);

        $content = HomePageContent::forStorefront();

        $this->assertSame('Hubungi Kami', $content['contactTitle']);
        $this->assertSame([...HomePageContent::HEADINGS, 'brands' => 'Merek Pilihan'], $content['headings']);

        // A blank heading saved directly (not through the form) falls back to its default.
        HomePageContent::save(['headings' => ['brands' => '  ']]);
        $this->assertSame('Brand Terlaris', HomePageContent::forStorefront()['headings']['brands']);
    }

    public function test_unknown_sections_are_ignored_and_contact_icons_are_kept_to_the_known_set(): void
    {
        HomePageContent::save(['hacked' => 'x', 'contacts' => [['icon' => 'skull', 'title' => 'CS', 'lines' => "a\n\n b "]]]);

        $content = HomePageContent::forStorefront();

        $this->assertArrayNotHasKey('hacked', $content);
        $this->assertSame([['icon' => 'phone', 'title' => 'CS', 'lines' => ['a', 'b']]], $content['contacts']);
        $this->assertCount(4, $content['hero']);
    }
}

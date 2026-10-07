<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Products\Pages\CreateProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class AdminTranslationTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    /**
     * Sample text the admin shows for data rather than for the interface
     * (placeholders in form fields). These stay as written in every language.
     */
    private const UNTRANSLATED_EXAMPLES = [
        'Apotek Inofarma Cinere', 'Apotek Sehat Bersama', 'Jakarta Barat', 'Jakarta Selatan',
        'Jl. Contoh No. 1', 'Jl. Jend. Sudirman Kav. 52-53', 'Jl. Kebon Jeruk Raya No. 27',
        'Kirana Wijaya', 'apotek@mail.com', 'kirana@mail.com',
    ];

    /**
     * @return list<string>
     */
    private function translationKeysInAdminCode(): array
    {
        $files = array_merge(
            glob(app_path('Filament/{,*/,*/*/,*/*/*/}*.php'), GLOB_BRACE) ?: [],
            glob(resource_path('views/filament/pages/*.php')) ?: [],
            [app_path('Support/AdminOptions.php')],
        );

        $keys = [];

        foreach ($files as $file) {
            preg_match_all("/__\\('((?:[^'\\\\]|\\\\.)*)'/", file_get_contents($file), $matches);

            foreach ($matches[1] as $key) {
                $keys[] = str_replace("\\'", "'", $key);
            }
        }

        return array_values(array_unique($keys));
    }

    public function test_every_admin_label_has_an_english_translation(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

        $missing = array_values(array_diff(
            $this->translationKeysInAdminCode(),
            array_keys($english),
            self::UNTRANSLATED_EXAMPLES,
        ));

        $this->assertSame([], $missing, 'Add these to lang/en.json: '.implode(' | ', $missing));
    }

    public function test_plural_labels_have_an_indonesian_fallback(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
        $indonesian = json_decode(file_get_contents(lang_path('id.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach (array_filter(array_keys($english), fn ($key) => str_starts_with($key, 'plural:')) as $key) {
            $this->assertArrayHasKey($key, $indonesian);
        }
    }

    public function test_the_panel_speaks_indonesian_by_default(): void
    {
        $this->seed();
        $this->signInAsAdmin();

        $this->get('/admin/hak-akses')
            ->assertOk()
            ->assertSee('Hak Akses')
            ->assertSee('Modul &amp; Aksi', false);
    }

    public function test_the_panel_switches_to_english(): void
    {
        $this->seed();
        $this->signInAsAdmin();
        $this->post(route('admin.bahasa', 'en'));

        $this->get('/admin/hak-akses')
            ->assertOk()
            ->assertSee('Access Rights')
            ->assertSee('Module &amp; Action', false)
            ->assertSee('Products')
            ->assertDontSee('Modul &amp; Aksi', false);
    }

    public function test_the_upload_box_keeps_its_technical_wording_in_english_even_in_indonesian(): void
    {
        $this->seed();
        $this->signInAsAdmin();

        $this->assertSame('id', app()->getLocale());

        $html = Livewire::test(CreateProduct::class)->html();

        // The label set the upload box loads is picked by this locale value.
        $this->assertStringContainsString("locale: 'en',", $html);
        $this->assertSame('id', app()->getLocale());
    }

    public function test_the_text_editor_toolbar_and_table_hints_use_english_technical_terms(): void
    {
        app()->setLocale('id');

        $this->assertSame('Link', __('filament-forms::components.rich_editor.tools.link'));
        $this->assertSame('Undo', __('filament-forms::components.rich_editor.tools.undo'));
        $this->assertSame('Heading 2', __('filament-forms::components.rich_editor.tools.h2'));
        $this->assertSame('Drag and drop the records into order.', __('filament-tables::table.reorder_indicator'));
        // Anything not overridden stays Filament's own Indonesian.
        $this->assertSame('Tabel', __('filament-forms::components.rich_editor.tools.table'));
    }
}

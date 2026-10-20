<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\EditFaqPage;
use App\Filament\Pages\EditPrivacyPolicyPage;
use App\Filament\Pages\EditRefundPolicyPage;
use App\Filament\Pages\EditShippingInfoPage;
use App\Filament\Pages\EditSiteFooterPage;
use App\Filament\Pages\EditTermsPage;
use App\Filament\Pages\WebsiteContent;
use App\Models\Role;
use App\Models\User;
use App\Support\DocumentPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class WebsitePagesContentTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_the_overview_lists_every_editable_page(): void
    {
        $component = Livewire::test(WebsiteContent::class)->assertOk();

        foreach (WebsiteContent::EDITORS as $editor) {
            $component->assertSee($editor::getNavigationLabel())->assertSee($editor::getUrl());
        }

        $this->assertCount(7, WebsiteContent::EDITORS);
    }

    public function test_the_faq_shows_the_shipped_questions_until_something_is_saved(): void
    {
        $this->get('/faq')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/Faq')
            ->has('faqs', 5)
            ->where('faqs.0.question', 'Apakah saya bisa memesan secara online?')
            ->where('faqs.0.paragraphs', ['Tentu, Anda bisa memesan langsung lewat situs web atau aplikasi kami kapan saja.']));
    }

    public function test_faq_questions_can_be_edited_reordered_and_removed(): void
    {
        $component = Livewire::test(EditFaqPage::class);
        $items = array_values($component->get('data.items'));

        $component
            ->set('data.items', [
                ['question' => 'Berapa lama pengiriman?', 'answer' => "Biasanya hari yang sama.\n\nTergantung kurir."],
                $items[0],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotified(__(':page disimpan.', ['page' => __('Konten FAQ')]));

        $this->get('/faq')->assertInertia(fn (AssertableInertia $page) => $page
            ->has('faqs', 2)
            ->where('faqs.0.question', 'Berapa lama pengiriman?')
            ->where('faqs.0.paragraphs', ['Biasanya hari yang sama.', 'Tergantung kurir.'])
            ->where('faqs.1.question', 'Apakah saya bisa memesan secara online?'));
    }

    public function test_a_faq_needs_at_least_one_question_and_every_answer(): void
    {
        $component = Livewire::test(EditFaqPage::class);
        $firstKey = array_key_first($component->get('data.items'));

        $component->set("data.items.{$firstKey}.answer", '')->call('save')->assertHasErrors(["data.items.{$firstKey}.answer"]);
        $component->set('data.items', [])->call('save')->assertHasErrors(['data.items']);
    }

    public function test_the_shipping_and_refund_pages_show_their_sections(): void
    {
        $this->get('/shipping-info')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/ShippingInfo')
            ->where('document.title', 'Kebijakan Pengiriman')
            ->has('document.sections', 6)
            ->where('document.sections.0.title', 'Ongkos Kirim'));

        $this->get('/kebijakan-pengembalian-dana')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/RefundPolicy')
            ->has('document.sections', 5)
            ->where('document.sections.4.title', 'Pertanyaan Status Refund'));
    }

    public function test_a_document_page_section_can_be_edited(): void
    {
        $component = Livewire::test(EditShippingInfoPage::class);
        $firstKey = array_key_first($component->get('data.sections'));

        $component
            ->set('data.title', 'Pengiriman & Pembayaran')
            ->set("data.sections.{$firstKey}.body", "Ongkir dihitung dari cabang.\n\nGratis ongkir untuk pesanan tertentu.")
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/shipping-info')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('document.title', 'Pengiriman & Pembayaran')
            ->where('document.sections.0.paragraphs', ['Ongkir dihitung dari cabang.', 'Gratis ongkir untuk pesanan tertentu.']));

        // The refund page is stored separately and is untouched.
        $this->get('/kebijakan-pengembalian-dana')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('document.title', 'Kebijakan Pengembalian Dana'));
    }

    public function test_the_legal_popups_load_numbered_documents_with_the_company_block(): void
    {
        $this->getJson('/konten/dokumen/terms')
            ->assertOk()
            ->assertJsonPath('title', 'Syarat & Ketentuan Inofarma')
            ->assertJsonCount(12, 'sections')
            ->assertJsonPath('sections.0.title', '1. Pendahuluan')
            ->assertJsonPath('sections.3.clauses.1.number', '4.2')
            ->assertJsonPath('updatedAt', 'November 2025')
            ->assertJsonPath('company.name', 'PT Teknologi Medika Pratama');

        $this->getJson('/konten/dokumen/privacy')
            ->assertOk()
            ->assertJsonCount(9, 'sections')
            ->assertJsonCount(6, 'sections.1.clauses.1.items');

        // Only the two legal documents are served this way.
        $this->getJson('/konten/dokumen/shipping')->assertNotFound();
    }

    public function test_legal_clauses_are_renumbered_after_reordering_and_the_date_follows_the_change(): void
    {
        Carbon::setTestNow('2026-10-19 10:00');

        $component = Livewire::test(EditTermsPage::class);
        $sections = array_values($component->get('data.sections'));

        $component
            ->set('data.sections', [$sections[1], $sections[0], ...array_slice($sections, 2)])
            ->call('save')
            ->assertHasNoErrors();

        $this->getJson('/konten/dokumen/terms')
            ->assertJsonPath('sections.0.title', '1. Definisi')
            ->assertJsonPath('sections.1.title', '2. Pendahuluan')
            ->assertJsonPath('sections.1.clauses.0.number', '2.1')
            ->assertJsonPath('updatedAt', 'Oktober 2026');

        // Saving again without changing the text keeps the date.
        Carbon::setTestNow('2026-12-01 10:00');
        Livewire::test(EditTermsPage::class)->call('save')->assertHasNoErrors();
        $this->getJson('/konten/dokumen/terms')->assertJsonPath('updatedAt', 'Oktober 2026');

        // The privacy policy is untouched.
        $this->getJson('/konten/dokumen/privacy')->assertJsonPath('updatedAt', 'November 2025');

        Carbon::setTestNow();
    }

    public function test_legal_bullet_points_are_written_one_per_line(): void
    {
        $component = Livewire::test(EditPrivacyPolicyPage::class);
        $sectionKey = array_key_first($component->get('data.sections'));
        $clauseKey = array_key_first($component->get("data.sections.{$sectionKey}.clauses"));

        $component
            ->set("data.sections.{$sectionKey}.clauses.{$clauseKey}.items", "Poin satu\n\n  Poin dua  \n")
            ->call('save')
            ->assertHasNoErrors();

        $this->getJson('/konten/dokumen/privacy')->assertJsonPath('sections.0.clauses.0.items', ['Poin satu', 'Poin dua']);
    }

    public function test_the_footer_and_company_details_can_be_changed(): void
    {
        $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('siteFooter.aboutTitle', 'Tentang Apotek Inofarma')
            ->has('siteFooter.links', 3)
            ->where('siteFooter.links.2.external', true)
            ->where('siteFooter.links.0.external', false));

        Livewire::test(EditSiteFooterPage::class)
            ->set('data.aboutText', 'Apotek terdekat untuk keluarga.')
            ->set('data.links', [['label' => 'Promo', 'href' => '/shop?promo=1'], ['label' => 'Instagram', 'href' => 'https://instagram.com/inofarma']])
            ->set('data.company.phone', '021-555-1234')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/shop')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('siteFooter.aboutText', 'Apotek terdekat untuk keluarga.')
            ->has('siteFooter.links', 2)
            ->where('siteFooter.links.1.external', true));

        $this->getJson('/konten/dokumen/terms')
            ->assertJsonPath('company.phone', '021-555-1234')
            ->assertJsonPath('company.name', 'PT Teknologi Medika Pratama');
    }

    public function test_a_footer_link_must_be_a_shop_path_or_a_full_address(): void
    {
        Livewire::test(EditSiteFooterPage::class)
            ->set('data.links', [['label' => 'Bahaya', 'href' => 'javascript:alert(1)']])
            ->call('save')
            ->assertHasErrors(['data.links.0.href']);
    }

    public function test_a_viewer_can_read_every_editor_but_save_none(): void
    {
        $this->signInWith(['Konten:Lihat']);

        foreach ([EditFaqPage::class, EditShippingInfoPage::class, EditRefundPolicyPage::class, EditTermsPage::class, EditPrivacyPolicyPage::class, EditSiteFooterPage::class] as $editor) {
            Livewire::test($editor)->assertOk()->call('save')->assertForbidden();
        }
    }

    public function test_someone_without_the_content_permission_cannot_open_the_editors(): void
    {
        $this->signInWith(['Produk:Lihat']);

        foreach (['faq', 'info-pengiriman', 'pengembalian-dana', 'syarat-ketentuan', 'kebijakan-privasi', 'footer'] as $slug) {
            $this->get("/admin/konten-website/{$slug}")->assertForbidden();
        }
    }

    public function test_an_unknown_document_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DocumentPageContent::forStorefront('rahasia');
    }
}

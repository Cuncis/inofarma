<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\Newsletters\Pages\CreateNewsletter;
use App\Filament\Resources\Newsletters\Pages\EditNewsletter;
use App\Filament\Resources\Newsletters\Pages\ListNewsletters;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Jobs\SendNewsletter;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Support\Newsletters\NewsletterRenderer;
use App\Support\Newsletters\SubscriberCsv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function csv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'subs');
        file_put_contents($path, $content);

        return $path;
    }

    // ---- subscribers -------------------------------------------------

    public function test_the_subscriber_list_shows_email_status_and_date(): void
    {
        $subscriber = Subscriber::factory()->create(['email' => 'ani@example.com']);
        Subscriber::factory()->unsubscribed()->create(['email' => 'budi@example.com']);

        Livewire::test(ListSubscribers::class)
            ->assertCanSeeTableRecords(Subscriber::all())
            ->assertSee('ani@example.com')
            ->assertSee('Berlangganan')
            ->assertSee('Berhenti')
            ->assertSee($subscriber->subscribed_at->translatedFormat('d M Y'));
    }

    public function test_subscribers_can_be_searched_by_email(): void
    {
        $ani = Subscriber::factory()->create(['email' => 'ani@example.com']);
        $budi = Subscriber::factory()->create(['email' => 'budi@example.com']);

        Livewire::test(ListSubscribers::class)
            ->searchTable('ani@')
            ->assertCanSeeTableRecords([$ani])
            ->assertCanNotSeeTableRecords([$budi]);
    }

    public function test_subscribers_can_be_filtered_by_status(): void
    {
        $active = Subscriber::factory()->create();
        $gone = Subscriber::factory()->unsubscribed()->create();

        Livewire::test(ListSubscribers::class)
            ->filterTable('status', Subscriber::UNSUBSCRIBED)
            ->assertCanSeeTableRecords([$gone])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_an_administrator_can_unsubscribe_a_contact_but_the_row_is_kept(): void
    {
        $subscriber = Subscriber::factory()->create();

        Livewire::test(ListSubscribers::class)
            ->callTableAction('unsubscribe', $subscriber);

        $subscriber->refresh();
        $this->assertSame(Subscriber::UNSUBSCRIBED, $subscriber->status);
        $this->assertNotNull($subscriber->unsubscribed_at);
        $this->assertDatabaseHas('subscribers', ['id' => $subscriber->id]);
    }

    public function test_csv_import_adds_new_addresses_and_skips_bad_and_existing_ones(): void
    {
        Subscriber::factory()->create(['email' => 'lama@example.com']);

        $result = SubscriberCsv::import($this->csv("email\nbaru@example.com\nLAMA@example.com\nbukan-email\n\nlain@example.com\n"));

        $this->assertSame(['imported' => 2, 'duplicates' => 1, 'invalid' => 1], $result);
        $this->assertDatabaseHas('subscribers', ['email' => 'baru@example.com', 'status' => 'berlangganan']);
    }

    public function test_csv_import_never_resubscribes_someone_who_opted_out(): void
    {
        Subscriber::factory()->unsubscribed()->create(['email' => 'pergi@example.com']);

        $result = SubscriberCsv::import($this->csv("pergi@example.com\n"));

        $this->assertSame(1, $result['duplicates']);
        $this->assertSame(Subscriber::UNSUBSCRIBED, Subscriber::where('email', 'pergi@example.com')->value('status'));
    }

    public function test_csv_import_reads_a_status_column_and_a_headerless_list(): void
    {
        SubscriberCsv::import($this->csv("email,status\na@example.com,berhenti\nb@example.com,berlangganan\n"));

        $this->assertSame('berhenti', Subscriber::where('email', 'a@example.com')->value('status'));
        $this->assertSame('berlangganan', Subscriber::where('email', 'b@example.com')->value('status'));

        SubscriberCsv::import($this->csv("c@example.com\nd@example.com\n"));
        $this->assertDatabaseHas('subscribers', ['email' => 'd@example.com']);
    }

    public function test_the_subscriber_export_downloads_a_csv_with_the_expected_columns(): void
    {
        Subscriber::factory()->create(['email' => 'ani@example.com']);

        ob_start();
        SubscriberCsv::export(Subscriber::query())();
        $csv = ob_get_clean();

        $this->assertStringContainsString('email,status,subscribed_at,unsubscribed_at', $csv);
        $this->assertStringContainsString('ani@example.com,berlangganan', $csv);
    }

    // ---- footer sign-up ----------------------------------------------

    public function test_a_visitor_can_subscribe_from_the_footer_form(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => '  Baru@Example.com '])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscribers', ['email' => 'baru@example.com', 'status' => 'berlangganan']);
    }

    public function test_subscribing_twice_keeps_one_row_and_gives_the_same_answer(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'ani@example.com']);
        $this->post(route('newsletter.subscribe'), ['email' => 'ani@example.com'])->assertSessionHas('success');

        $this->assertSame(1, Subscriber::where('email', 'ani@example.com')->count());
    }

    public function test_someone_who_unsubscribed_can_choose_to_come_back(): void
    {
        $subscriber = Subscriber::factory()->unsubscribed()->create(['email' => 'kembali@example.com']);

        $this->post(route('newsletter.subscribe'), ['email' => 'kembali@example.com'])->assertSessionHas('success');

        $subscriber->refresh();
        $this->assertSame(Subscriber::SUBSCRIBED, $subscriber->status);
        $this->assertNull($subscriber->unsubscribed_at);
    }

    public function test_the_footer_form_rejects_a_missing_or_malformed_email(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'bukan-email'])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    // ---- unsubscribe link --------------------------------------------

    public function test_opening_the_unsubscribe_link_only_asks_for_confirmation(): void
    {
        $subscriber = Subscriber::factory()->create();

        $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee('Berhenti berlangganan?');

        $this->assertSame(Subscriber::SUBSCRIBED, $subscriber->fresh()->status);
    }

    public function test_confirming_unsubscribes_and_records_the_date(): void
    {
        $subscriber = Subscriber::factory()->create();

        $this->post(route('newsletter.unsubscribe.store', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee('sudah berhenti berlangganan');

        $subscriber->refresh();
        $this->assertSame(Subscriber::UNSUBSCRIBED, $subscriber->status);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }

    public function test_an_unknown_unsubscribe_token_is_a_404(): void
    {
        $this->get(route('newsletter.unsubscribe', 'tidak-ada'))->assertNotFound();
    }

    // ---- creating and editing ----------------------------------------

    public function test_a_newsletter_can_be_created_as_a_draft(): void
    {
        Livewire::test(CreateNewsletter::class)
            ->fillForm([
                'name' => 'Promo Oktober',
                'subject' => 'Diskon vitamin 20%',
                'preview_text' => 'Hanya minggu ini',
                'content' => '<p>Halo semua</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('newsletters', ['name' => 'Promo Oktober', 'status' => 'draf']);
    }

    public function test_name_subject_and_content_are_required(): void
    {
        Livewire::test(CreateNewsletter::class)
            ->fillForm(['name' => '', 'subject' => '', 'content' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'subject' => 'required', 'content']);
    }

    // ---- email -------------------------------------------------------

    public function test_every_newsletter_email_carries_a_working_unsubscribe_link(): void
    {
        $newsletter = Newsletter::factory()->create(['content' => '<p>Isi</p>']);
        $subscriber = Subscriber::factory()->create();

        $mail = new NewsletterMail($newsletter, $subscriber);
        $html = $mail->render();

        $link = route('newsletter.unsubscribe', $subscriber->unsubscribe_token);
        $this->assertStringContainsString($link, $html);
        $this->assertStringContainsString($newsletter->preview_text, $html);
        $this->assertSame('<'.$link.'>', $mail->headers()->text['List-Unsubscribe']);
    }

    public function test_the_button_block_renders_in_the_email(): void
    {
        $content = '<div data-type="customBlock" data-id="button" data-config="'
            .e(json_encode(['label' => 'Belanja Sekarang', 'url' => 'https://inofarma.com/shop'])).'"></div>';
        $newsletter = Newsletter::factory()->create(['content' => $content]);

        $html = (new NewsletterMail($newsletter, Subscriber::factory()->create()))->render();

        $this->assertStringContainsString('Belanja Sekarang', $html);
        $this->assertStringContainsString('https://inofarma.com/shop', $html);
        // The editor's HTML cleaner drops `bgcolor`, so the green has to survive as an inline style
        // or the white label ends up on a white background.
        $this->assertMatchesRegularExpression('/<a [^>]*background-color:#24CE30[^>]*color:#ffffff/', $html);
    }

    public function test_pasted_html_replaces_the_editor_content_and_keeps_the_unsubscribe_link(): void
    {
        $newsletter = Newsletter::factory()->create([
            'content' => '<p>Isi dari editor</p>',
            'custom_html' => '<table><tr><td style="color:#ff0000">Halo dari HTML kustom</td></tr></table>',
        ]);
        $subscriber = Subscriber::factory()->create();

        $html = (new NewsletterMail($newsletter, $subscriber))->render();

        $this->assertStringContainsString('Halo dari HTML kustom', $html);
        $this->assertStringContainsString('color:#ff0000', $html);
        $this->assertStringNotContainsString('Isi dari editor', $html);
        $this->assertStringContainsString(route('newsletter.unsubscribe', $subscriber->unsubscribe_token), $html);
    }

    public function test_pasted_css_blocks_move_to_the_head_and_the_layout_styles_do_not_fight_them(): void
    {
        $newsletter = Newsletter::factory()->create([
            'custom_html' => '<html><head><style>.promo { background:#123456; }</style></head><body><div class="promo">Diskon</div></body></html>',
        ]);

        $html = (new NewsletterMail($newsletter, Subscriber::factory()->create()))->render();

        $this->assertLessThan(strpos($html, '</head>'), strpos($html, '.promo { background:#123456; }'));
        $this->assertStringContainsString('<div class="promo">Diskon</div>', $html);
        $this->assertStringNotContainsString('<html><head>', $html);
        $this->assertStringNotContainsString('.nl p {', $html);
    }

    public function test_scripts_and_event_handlers_in_pasted_html_are_removed(): void
    {
        $parts = NewsletterRenderer::custom('<p onclick="steal()">Aman</p><script>alert(1)</script><a href="javascript:alert(2)">klik</a>');

        $this->assertStringContainsString('Aman', $parts['body']);
        $this->assertStringNotContainsString('<script', $parts['body']);
        $this->assertStringNotContainsString('onclick', $parts['body']);
        $this->assertStringNotContainsString('javascript:', $parts['body']);
    }

    public function test_the_editor_is_not_required_when_html_is_pasted(): void
    {
        Livewire::test(CreateNewsletter::class)
            ->fillForm([
                'name' => 'Pakai HTML', 'subject' => 'Halo', 'content' => '',
                'custom_html' => '<p>Halo</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('newsletters', ['name' => 'Pakai HTML', 'custom_html' => '<p>Halo</p>']);
    }

    public function test_a_test_email_goes_only_to_the_addresses_given(): void
    {
        Mail::fake();
        Subscriber::factory()->create(['email' => 'pelanggan@example.com']);
        $newsletter = Newsletter::factory()->create();

        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])
            ->callAction('sendTest', data: ['emails' => ['tim@example.com']])
            ->assertHasNoActionErrors();

        Mail::assertSent(NewsletterMail::class, fn (NewsletterMail $mail) => $mail->hasTo('tim@example.com') && $mail->isTest);
        Mail::assertNotSent(NewsletterMail::class, fn (NewsletterMail $mail) => $mail->hasTo('pelanggan@example.com'));
    }

    // ---- sending -----------------------------------------------------

    public function test_sending_queues_the_job_and_records_the_recipient_count(): void
    {
        Queue::fake();
        Subscriber::factory()->count(3)->create();
        Subscriber::factory()->unsubscribed()->create();
        $newsletter = Newsletter::factory()->create();

        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])
            ->callAction('send');

        Queue::assertPushed(SendNewsletter::class, fn (SendNewsletter $job) => $job->newsletterId === $newsletter->id);

        $newsletter->refresh();
        $this->assertSame(Newsletter::SENDING, $newsletter->status);
        $this->assertSame(3, $newsletter->recipient_count);
    }

    public function test_the_confirmation_states_how_many_people_will_receive_it(): void
    {
        Subscriber::factory()->count(2)->create();
        Subscriber::factory()->unsubscribed()->create();
        $newsletter = Newsletter::factory()->create();

        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])
            ->mountAction('send')
            ->assertMountedActionModalSee('dikirim ke 2 pelanggan');
    }

    public function test_sending_with_no_subscribers_is_refused(): void
    {
        Queue::fake();
        $newsletter = Newsletter::factory()->create();

        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])->callAction('send');

        Queue::assertNothingPushed();
        $this->assertSame(Newsletter::DRAFT, $newsletter->fresh()->status);
    }

    public function test_a_newsletter_that_was_already_sent_cannot_be_sent_again(): void
    {
        Queue::fake();
        Subscriber::factory()->create();
        $newsletter = Newsletter::factory()->create(['status' => Newsletter::SENT]);

        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])
            ->assertActionHidden('send');

        Queue::assertNothingPushed();
    }

    public function test_the_job_mails_only_current_subscribers_and_records_the_results(): void
    {
        Mail::fake();
        $a = Subscriber::factory()->create(['email' => 'a@example.com']);
        $b = Subscriber::factory()->create(['email' => 'b@example.com']);
        Subscriber::factory()->unsubscribed()->create(['email' => 'pergi@example.com']);
        $newsletter = Newsletter::factory()->create(['status' => Newsletter::SENDING, 'recipient_count' => 2]);

        (new SendNewsletter($newsletter->id))->handle();

        Mail::assertSent(NewsletterMail::class, 2);
        Mail::assertNotSent(NewsletterMail::class, fn (NewsletterMail $mail) => $mail->hasTo('pergi@example.com'));

        $newsletter->refresh();
        $this->assertSame(Newsletter::SENT, $newsletter->status);
        $this->assertSame(2, $newsletter->success_count);
        $this->assertSame(0, $newsletter->failed_count);
        $this->assertNotNull($newsletter->sent_at);
        $this->assertDatabaseHas('newsletter_deliveries', ['newsletter_id' => $newsletter->id, 'subscriber_id' => $a->id, 'status' => 'terkirim']);
        $this->assertDatabaseHas('newsletter_deliveries', ['newsletter_id' => $newsletter->id, 'subscriber_id' => $b->id, 'status' => 'terkirim']);
    }

    public function test_running_the_job_twice_never_mails_anyone_twice(): void
    {
        Mail::fake();
        Subscriber::factory()->count(2)->create();
        $newsletter = Newsletter::factory()->create(['status' => Newsletter::SENDING]);

        (new SendNewsletter($newsletter->id))->handle();
        (new SendNewsletter($newsletter->id))->handle();

        Mail::assertSent(NewsletterMail::class, 2);
    }

    public function test_one_failing_address_is_counted_and_does_not_stop_the_rest(): void
    {
        $good = Subscriber::factory()->create(['email' => 'baik@example.com']);
        $bad = Subscriber::factory()->create(['email' => 'rusak@example.com']);
        $newsletter = Newsletter::factory()->create(['status' => Newsletter::SENDING]);

        Mail::shouldReceive('to')->with('baik@example.com')->once()->andReturnSelf();
        Mail::shouldReceive('to')->with('rusak@example.com')->once()->andThrow(new \RuntimeException('Mailbox full'));
        Mail::shouldReceive('send')->once();

        (new SendNewsletter($newsletter->id))->handle();

        $newsletter->refresh();
        $this->assertSame(Newsletter::SENT, $newsletter->status);
        $this->assertSame(1, $newsletter->success_count);
        $this->assertSame(1, $newsletter->failed_count);
        $this->assertDatabaseHas('newsletter_deliveries', ['subscriber_id' => $bad->id, 'status' => 'gagal', 'error' => 'Mailbox full']);
    }

    public function test_a_send_where_nothing_got_through_is_marked_failed(): void
    {
        Subscriber::factory()->create(['email' => 'rusak@example.com']);
        $newsletter = Newsletter::factory()->create(['status' => Newsletter::SENDING]);

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        (new SendNewsletter($newsletter->id))->handle();

        $this->assertSame(Newsletter::FAILED, $newsletter->fresh()->status);
    }

    // ---- history -----------------------------------------------------

    public function test_campaign_history_shows_status_dates_and_totals(): void
    {
        $sent = Newsletter::factory()->create([
            'name' => 'Promo Lebaran', 'subject' => 'Diskon Lebaran', 'status' => Newsletter::SENT,
            'sent_at' => '2026-10-01 09:30:00', 'recipient_count' => 120, 'success_count' => 118, 'failed_count' => 2,
        ]);
        Newsletter::factory()->create(['name' => 'Rancangan', 'status' => Newsletter::DRAFT]);

        Livewire::test(ListNewsletters::class)
            ->assertCanSeeTableRecords(Newsletter::all())
            ->assertSee('Promo Lebaran')
            ->assertSee('Diskon Lebaran')
            ->assertSee('Terkirim')
            ->assertSee('Draf')
            ->assertSee('01 Okt 2026')
            ->assertSee('120')
            ->assertSee('118');
    }
}

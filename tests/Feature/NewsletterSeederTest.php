<?php

namespace Tests\Feature;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Database\Seeders\NewsletterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_dummy_subscribers_and_newsletter_templates(): void
    {
        $this->seed(NewsletterSeeder::class);

        $this->assertSame(24, Subscriber::subscribed()->count());
        $this->assertSame(6, Subscriber::where('status', Subscriber::UNSUBSCRIBED)->whereNotNull('unsubscribed_at')->count());
        $this->assertSame(0, Subscriber::where('email', 'not like', '%@example.com')->count());
        $this->assertSame(3, Newsletter::where('status', Newsletter::DRAFT)->count());
        $this->assertSame(1, Newsletter::where('status', Newsletter::SENT)->count());
    }

    public function test_running_it_twice_adds_nothing_new(): void
    {
        $this->seed(NewsletterSeeder::class);
        $this->seed(NewsletterSeeder::class);

        $this->assertSame(30, Subscriber::count());
        $this->assertSame(4, Newsletter::count());
    }

    public function test_it_does_not_resubscribe_or_overwrite_an_existing_row(): void
    {
        Subscriber::factory()->unsubscribed()->create(['email' => 'ani.wijaya@example.com']);
        Newsletter::factory()->create(['name' => 'Contoh: Selamat Datang', 'subject' => 'Sudah saya ubah']);

        $this->seed(NewsletterSeeder::class);

        $this->assertSame(Subscriber::UNSUBSCRIBED, Subscriber::where('email', 'ani.wijaya@example.com')->value('status'));
        $this->assertSame('Sudah saya ubah', Newsletter::where('name', 'Contoh: Selamat Datang')->value('subject'));
    }

    public function test_the_template_button_renders_in_the_email(): void
    {
        $this->seed(NewsletterSeeder::class);

        $newsletter = Newsletter::where('name', 'Contoh: Selamat Datang')->first();
        $html = (new NewsletterMail($newsletter, Subscriber::first()))->render();

        $this->assertStringContainsString('Mulai Belanja', $html);
        $this->assertStringContainsString('https://inofarma.com/shop', $html);
    }
}

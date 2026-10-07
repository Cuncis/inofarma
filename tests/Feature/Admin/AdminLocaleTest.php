<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class AdminLocaleTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_admin_panel_defaults_to_indonesian(): void
    {
        $this->get('/admin')->assertOk();

        $this->assertSame('id', app()->getLocale());
    }

    public function test_choosing_english_is_remembered_for_the_session(): void
    {
        $this->from('/admin')->post(route('admin.bahasa', 'en'))->assertRedirect('/admin');

        $this->get('/admin')->assertOk();

        $this->assertSame('en', app()->getLocale());
        $this->assertSame('en', session('admin_locale'));
    }

    public function test_switching_back_to_indonesian_works(): void
    {
        $this->post(route('admin.bahasa', 'en'));
        $this->post(route('admin.bahasa', 'id'));

        $this->get('/admin')->assertOk();

        $this->assertSame('id', app()->getLocale());
    }

    public function test_an_unsupported_language_is_rejected(): void
    {
        $this->post(route('admin.bahasa', 'fr'))->assertNotFound();

        $this->assertNull(session('admin_locale'));
    }

    public function test_the_language_switch_requires_a_signed_in_staff_member(): void
    {
        auth()->guard('web')->logout();
        $this->flushSession();

        $this->post(route('admin.bahasa', 'en'))->assertRedirect(route('admin.masuk'));
    }

    public function test_the_profile_menu_offers_both_languages(): void
    {
        $this->get('/admin')
            ->assertSee('Bahasa Indonesia')
            ->assertSee('English');
    }

    public function test_the_dashboard_no_longer_shows_the_welcome_widget(): void
    {
        $this->get('/admin')
            ->assertDontSee('Selamat Datang')
            ->assertDontSee('filament-info');
    }
}

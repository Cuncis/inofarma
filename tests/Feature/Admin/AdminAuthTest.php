<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Super Admin so a signed-in user can reach every permission-gated
     * screen this suite touches — this test is about the session mechanics,
     * not the permission matrix (that's RoleCrudTest).
     */
    private function makeUser(array $overrides = []): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => true,
            ...$overrides,
        ]);
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));

        return $user;
    }

    private function signIn(User $user, string $password = 'password'): self
    {
        $this->post('/admin/masuk', ['email' => $user->email, 'password' => $password]);

        return $this;
    }

    public function test_the_login_screen_is_reachable_without_signing_in(): void
    {
        $this->get('/admin/masuk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Admin/AuthSignIn'));
    }

    public function test_valid_credentials_sign_in(): void
    {
        $user = $this->makeUser();

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/admin')
            ->assertSessionHasNoErrors();

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertTrue(Auth::guard('web')->user()->is($user));
    }

    /**
     * `/admin` is the Filament panel, not an Inertia page — a real Inertia
     * client-side visit (the browser form on Admin/AuthSignIn.jsx) must land
     * there via `X-Inertia-Location` (a 409), not a plain 3xx `Location`
     * redirect. A plain redirect gets followed by Inertia's own client-side
     * fetch instead of triggering a real browser navigation, which renders
     * the Filament page's full HTML squashed into the current Inertia
     * layout — looking like a popup instead of a full page.
     */
    public function test_a_real_inertia_visit_gets_an_x_inertia_location_redirect_to_the_panel(): void
    {
        $user = $this->makeUser();

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'], [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => '1',
        ])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/admin'));
    }

    public function test_the_wrong_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_an_unknown_email_is_rejected(): void
    {
        $this->post('/admin/masuk', ['email' => 'tidak-ada@inofarma.co.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_a_deactivated_account_cannot_sign_in(): void
    {
        $user = $this->makeUser(['is_active' => false]);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_signing_in_still_requires_an_email_and_a_password(): void
    {
        $this->post('/admin/masuk', ['email' => 'bukan-email', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_five_failed_attempts_lock_out_a_sixth(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('web')->check());

        RateLimiter::clear('admin-login:127.0.0.1');
    }

    public function test_the_admin_area_is_closed_to_anonymous_visitors(): void
    {
        foreach (['/admin', '/admin/produk', '/admin/produk/create', '/admin/kategori', '/admin/pesanan'] as $path) {
            $this->get($path)->assertRedirect(route('admin.masuk'));
        }
    }

    public function test_signing_in_returns_you_to_where_you_were_headed(): void
    {
        $user = $this->makeUser();

        $this->get('/admin/kategori')->assertRedirect(route('admin.masuk'));

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(url('/admin/kategori'));
    }

    /**
     * `/admin` is the Filament panel's own dashboard now (a Livewire page,
     * not an Inertia response) — this is a plain HTTP smoke test rather than
     * an `assertInertia()` component check.
     */
    public function test_a_signed_in_admin_reaches_the_dashboard(): void
    {
        $this->signIn($this->makeUser())
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Whoops', false);
    }

    public function test_visiting_login_while_signed_in_goes_to_the_dashboard(): void
    {
        $this->signIn($this->makeUser())
            ->get('/admin/masuk')
            ->assertRedirect('/admin');
    }

    public function test_signing_out_closes_the_area_again(): void
    {
        $this->signIn($this->makeUser())->post('/admin/keluar')->assertRedirect(route('admin.masuk'));

        $this->assertFalse(Auth::guard('web')->check());
        $this->get('/admin')->assertRedirect(route('admin.masuk'));
    }

    public function test_an_account_with_two_factor_confirmed_is_sent_to_the_challenge_instead_of_logging_in(): void
    {
        $secret = (new Google2FA)->generateSecretKey();
        $user = $this->makeUser([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dua-faktor'));

        $this->assertFalse(Auth::guard('web')->check());

        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post('/admin/dua-faktor', ['code' => $code])
            ->assertRedirect('/admin');

        $this->assertTrue(Auth::guard('web')->check());
    }

    /**
     * Same reasoning as the plain sign-in's Inertia-visit test — the 2FA
     * challenge form is also an Inertia page, and its success redirect also
     * lands on the non-Inertia Filament panel.
     */
    public function test_a_real_inertia_2fa_confirmation_gets_an_x_inertia_location_redirect(): void
    {
        $secret = (new Google2FA)->generateSecretKey();
        $user = $this->makeUser([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);
        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post('/admin/dua-faktor', ['code' => $code], [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => '1',
        ])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/admin'));
    }

    public function test_the_storefront_is_unaffected_by_the_admin_guard(): void
    {
        $this->get('/')->assertOk();
        $this->get('/ui/shop')->assertOk();
        $this->get('/ui/signin')->assertOk();
    }

    public function test_the_removed_demo_pages_are_gone(): void
    {
        $user = $this->makeUser();

        foreach ([
            '/admin/halaman/selamat-datang',
            '/admin/halaman/segera-hadir',
            '/admin/halaman/linimasa',
            '/admin/halaman/harga',
            '/admin/halaman/pemeliharaan',
            '/admin/halaman/404',
            '/admin/auth/daftar',
            '/admin/auth/kunci-layar',
        ] as $path) {
            $this->signIn($user)->get($path)->assertNotFound();
        }
    }
}

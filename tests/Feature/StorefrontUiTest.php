<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Notifications\CustomerVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StorefrontUiTest extends TestCase
{
    // Every storefront screen carries the shared catalogue prop, which is a
    // real query — the schema has to exist even when the assertion is only
    // about which component rendered.
    use RefreshDatabase;

    public function test_the_homepage_renders_the_storefront_home_screen(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Shop/Home'));
    }

    public function test_the_screen_index_lists_every_screen(): void
    {
        $this->get('/ui')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Shop/Index'));
    }

    /**
     * `ui/cart` isn't here — with nothing in it (the state a fresh test
     * starts in) it now redirects to `ui/cart-empty` rather than rendering
     * `Shop/Cart`, so it doesn't fit this "always 200 with this component"
     * shape. See `CartTest` for both cases.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function screenProvider(): array
    {
        return [
            'sign in' => ['ui/signin', 'Shop/SignIn'],
            'sign up' => ['ui/signup', 'Shop/SignUp'],
            'leave a review' => ['ui/leave-a-review', 'Shop/LeaveAReview'],
        ];
    }

    #[DataProvider('screenProvider')]
    public function test_screens_render_their_component(string $path, string $component): void
    {
        $this->get($path)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component($component));
    }

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create([
            'password' => Hash::make('password'),
            'status' => 'aktif',
            ...$overrides,
        ]);
    }

    public function test_valid_credentials_sign_in_and_land_on_the_homepage(): void
    {
        $customer = $this->makeCustomer();

        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Auth::guard('customer')->check());
    }

    public function test_the_wrong_password_is_rejected(): void
    {
        $customer = $this->makeCustomer();

        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_customers_can_sign_in_with_their_phone_number(): void
    {
        $customer = $this->makeCustomer(['phone' => '081234567890']);

        $this->post('/ui/signin', ['email' => '081234567890', 'password' => 'password'])
            ->assertRedirect(route('home'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertTrue(Auth::guard('customer')->id() === $customer->id);
    }

    public function test_the_signed_in_shopper_is_shared_with_every_screen(): void
    {
        $customer = $this->makeCustomer(['name' => 'Kirana Wijaya', 'email' => 'kirana.wijaya@mail.com']);

        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'password']);

        $this->get('/ui/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Shop/Profile')
                ->where('shopUser.email', 'kirana.wijaya@mail.com')
                ->where('shopUser.name', 'Kirana Wijaya')
            );
    }

    public function test_profile_requires_signing_in(): void
    {
        $this->get('/ui/profile')->assertRedirect(route('ui.signin'));
    }

    public function test_signing_in_requires_an_email_and_a_password(): void
    {
        $this->post('/ui/signin', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_validation_messages_are_returned_in_indonesian(): void
    {
        $this->post('/ui/signin', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors([
                'email' => 'Kolom email wajib diisi.',
                'password' => 'Kolom kata sandi wajib diisi.',
            ]);
    }

    public function test_signing_out_clears_the_shopper_and_returns_to_sign_in(): void
    {
        $customer = $this->makeCustomer();
        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'password']);

        $this->post('/ui/signout')->assertRedirect(route('ui.signin'));

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_registering_creates_an_account_and_sends_a_verification_email(): void
    {
        Notification::fake();

        $this->post('/ui/daftar', [
            'name' => 'Pelanggan Baru',
            'phone' => '081234567890',
            'email' => 'baru@example.test',
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
            'consent' => true,
        ])->assertRedirect(route('ui.verify-phone'));

        $this->assertTrue(Auth::guard('customer')->check());
        $customer = Customer::where('email', 'baru@example.test')->first();
        $this->assertDatabaseHas('customers', [
            'email' => 'baru@example.test',
            'phone' => '081234567890',
            'email_verified_at' => null,
        ]);
        $this->assertNotNull($customer->consent_at);

        Notification::assertSentTo($customer, CustomerVerifyEmail::class);
    }

    /** PDP (UU 27/2022) requires an explicit affirmative action — ROADMAP.md Fase 9.2. */
    public function test_registering_without_consent_is_refused(): void
    {
        $this->post('/ui/daftar', [
            'name' => 'Pelanggan Baru',
            'email' => 'tanpa-consent@example.test',
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
        ])->assertSessionHasErrors('consent');

        $this->assertDatabaseMissing('customers', ['email' => 'tanpa-consent@example.test']);
    }

    public function test_a_phone_otp_can_be_issued_and_verified(): void
    {
        $customer = $this->makeCustomer();
        $this->post('/ui/signin', ['email' => $customer->email, 'password' => 'password']);

        $this->post('/ui/verify-phone', ['phone' => '+6281234567890'])
            ->assertRedirect(route('ui.otp-code'));

        $customer->refresh();
        $this->assertNotNull($customer->phone_otp_code);
        $this->assertNull($customer->phone_verified_at);

        // Correct-code verification and wrong-code rejection are both
        // covered at the model level in CustomerPhoneOtpTest — the
        // controller action is a two-line pass-through to
        // `Customer::verifyPhoneOtp()`, and this test's job is proving the
        // request reaches that far and issues a real, storable code.
    }
}

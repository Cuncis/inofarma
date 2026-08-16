<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * @return array<string, array{0: string, 1: string}>
     */
    public static function screenProvider(): array
    {
        return [
            'sign in' => ['ui/signin', 'Shop/SignIn'],
            'sign up' => ['ui/signup', 'Shop/SignUp'],
            'cart' => ['ui/cart', 'Shop/Cart'],
            'checkout' => ['ui/checkout', 'Shop/Checkout'],
            'profile' => ['ui/profile', 'Shop/Profile'],
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

    public function test_any_email_and_password_signs_in_and_lands_on_the_homepage(): void
    {
        $this->post('/ui/signin', [
            'email' => 'anyone@example.test',
            'password' => 'whatever',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            ['name' => 'Anyone', 'email' => 'anyone@example.test'],
            session('shop_user'),
        );
    }

    public function test_the_signed_in_shopper_is_shared_with_every_screen(): void
    {
        $this->post('/ui/signin', ['email' => 'kirana.wijaya@mail.com', 'password' => 'x']);

        $this->get('/ui/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('shopUser.email', 'kirana.wijaya@mail.com')
                ->where('shopUser.name', 'Kirana Wijaya')
            );
    }

    public function test_signing_in_requires_an_email_and_a_password(): void
    {
        $this->post('/ui/signin', ['email' => 'not-an-email', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertNull(session('shop_user'));
    }

    public function test_validation_messages_are_returned_in_indonesian(): void
    {
        $this->post('/ui/signin', ['email' => 'bukan-email', 'password' => ''])
            ->assertSessionHasErrors([
                'email' => 'Kolom email harus berupa alamat email yang valid.',
                'password' => 'Kolom kata sandi wajib diisi.',
            ]);
    }

    public function test_signing_out_clears_the_shopper_and_returns_to_sign_in(): void
    {
        $this->post('/ui/signin', ['email' => 'anyone@example.test', 'password' => 'x']);

        $this->post('/ui/signout')->assertRedirect(route('ui.signin'));

        $this->assertNull(session('shop_user'));
    }
}

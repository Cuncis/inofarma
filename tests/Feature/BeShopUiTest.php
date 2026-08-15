<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BeShopUiTest extends TestCase
{
    public function test_the_homepage_renders_the_beshop_home_screen(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('BeShop/Home'));
    }

    public function test_the_screen_index_lists_every_screen(): void
    {
        $this->get('/ui')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('BeShop/Index'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function screenProvider(): array
    {
        return [
            'sign in' => ['ui/signin', 'BeShop/SignIn'],
            'sign up' => ['ui/signup', 'BeShop/SignUp'],
            'cart' => ['ui/cart', 'BeShop/Cart'],
            'checkout' => ['ui/checkout', 'BeShop/Checkout'],
            'profile' => ['ui/profile', 'BeShop/Profile'],
            'leave a review' => ['ui/leave-a-review', 'BeShop/LeaveAReview'],
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
            session('beshop_user'),
        );
    }

    public function test_the_signed_in_shopper_is_shared_with_every_screen(): void
    {
        $this->post('/ui/signin', ['email' => 'kristin.watson@mail.com', 'password' => 'x']);

        $this->get('/ui/profile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('beshopUser.email', 'kristin.watson@mail.com')
                ->where('beshopUser.name', 'Kristin Watson')
            );
    }

    public function test_signing_in_requires_an_email_and_a_password(): void
    {
        $this->post('/ui/signin', ['email' => 'not-an-email', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertNull(session('beshop_user'));
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

        $this->assertNull(session('beshop_user'));
    }
}

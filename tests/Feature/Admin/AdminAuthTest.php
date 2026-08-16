<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    // The guarded screens query the database now, so the schema has to exist —
    // the tables can stay empty, this suite is only about who gets through.
    use RefreshDatabase;

    /**
     * Sign in as anyone — the prototype accepts any credentials.
     */
    private function signIn(string $email = 'dwi.lestari@inofarma.co.id'): self
    {
        $this->post('/admin/masuk', ['email' => $email, 'password' => 'apa saja']);

        return $this;
    }

    public function test_the_login_screen_is_reachable_without_signing_in(): void
    {
        $this->get('/admin/masuk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Admin/AuthSignIn'));
    }

    public function test_any_email_and_password_signs_in(): void
    {
        $this->post('/admin/masuk', [
            'email' => 'siapa.saja@inofarma.co.id',
            'password' => 'terserah',
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            ['name' => 'Siapa Saja', 'email' => 'siapa.saja@inofarma.co.id'],
            session('admin_user'),
        );
    }

    public function test_signing_in_still_requires_an_email_and_a_password(): void
    {
        $this->post('/admin/masuk', ['email' => 'bukan-email', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertNull(session('admin_user'));
    }

    public function test_the_admin_area_is_closed_to_anonymous_visitors(): void
    {
        foreach (['/admin', '/admin/produk', '/admin/kategori', '/admin/pesanan'] as $path) {
            $this->get($path)->assertRedirect(route('admin.masuk'));
        }
    }

    public function test_writes_are_closed_to_anonymous_visitors_too(): void
    {
        $this->post('/admin/produk', ['name' => 'Seharusnya Gagal'])
            ->assertRedirect(route('admin.masuk'));

        $this->delete('/admin/produk/PRD-001')->assertRedirect(route('admin.masuk'));
    }

    public function test_signing_in_returns_you_to_where_you_were_headed(): void
    {
        $this->get('/admin/kategori')->assertRedirect(route('admin.masuk'));

        $this->post('/admin/masuk', ['email' => 'admin@inofarma.co.id', 'password' => 'x'])
            ->assertRedirect(url('/admin/kategori'));
    }

    public function test_a_signed_in_admin_reaches_the_dashboard(): void
    {
        $this->signIn()
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Admin/Dashboard'));
    }

    public function test_the_signed_in_admin_is_shared_with_every_screen(): void
    {
        $this->signIn('kirana.wijaya@inofarma.co.id')
            ->get('/admin/produk')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('adminUser.name', 'Kirana Wijaya')
                ->where('adminUser.email', 'kirana.wijaya@inofarma.co.id')
            );
    }

    public function test_visiting_login_while_signed_in_goes_to_the_dashboard(): void
    {
        $this->signIn()
            ->get('/admin/masuk')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_signing_out_closes_the_area_again(): void
    {
        $this->signIn()->post('/admin/keluar')->assertRedirect(route('admin.masuk'));

        $this->assertNull(session('admin_user'));
        $this->get('/admin')->assertRedirect(route('admin.masuk'));
    }

    public function test_the_storefront_is_unaffected_by_the_admin_guard(): void
    {
        $this->get('/')->assertOk();
        $this->get('/ui/shop')->assertOk();
        $this->get('/ui/signin')->assertOk();
    }

    public function test_the_removed_demo_pages_are_gone(): void
    {
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
            $this->signIn()->get($path)->assertNotFound();
        }
    }
}

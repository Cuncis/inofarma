<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * "Kebijakan Privasi" is a standalone page at /pp (it replaced the old
 * popup). Its text is fixed legal copy from App\Support\PrivacyPolicyContent.
 */
class PrivacyPolicyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_privacy_policy_page_renders_its_fixed_content(): void
    {
        $this->get('/pp')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Shop/PrivacyPolicy')
            ->where('document.title', 'Kebijakan Privasi')
            ->where('document.updatedAt', '17 April 2025')
            ->where('document.intro.0.type', 'paragraph')
            ->where('document.sections.0.title', 'Pengumpulan Data Pribadi')
            ->where('document.sections.0.blocks.1.type', 'list')
            ->where('document.sections.0.blocks.1.items.0', 'ingin menerima katalog Kami;')
            ->has('document.sections', 13));
    }

    public function test_the_privacy_policy_is_public(): void
    {
        // Reachable from signup/checkout before a shopper has an account.
        $this->get('/pp')->assertOk();
    }
}

<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\Security;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * A staff member's own 2FA self-service, exercised through the Filament page
 * at /admin/keamanan. Mirrors TwoFactorController's enable/confirm/
 * disable/regenerate lifecycle — the legacy Inertia screen itself has no
 * dedicated test file, only the login-time challenge (AdminAuthTest).
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_2fa_starts_disabled_with_only_the_activate_action(): void
    {
        Livewire::test(Security::class)
            ->assertActionVisible('activate')
            ->assertActionHidden('confirm')
            ->assertActionHidden('disable')
            ->assertActionHidden('regenerate');
    }

    public function test_activating_generates_a_secret_and_recovery_codes_but_does_not_enable_it_yet(): void
    {
        $user = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(Security::class)
            ->callAction('activate');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasEnabledTwoFactor());
    }

    public function test_confirming_the_correct_code_enables_two_factor(): void
    {
        $user = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(Security::class)->callAction('activate');
        $user->refresh();

        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);

        Livewire::test(Security::class)
            ->assertActionVisible('confirm')
            ->callAction('confirm', data: ['code' => $code])
            ->assertHasNoActionErrors();

        $this->assertTrue($user->fresh()->hasEnabledTwoFactor());
        $this->assertDatabaseHas('audit_logs', ['action' => '2fa_confirmed', 'auditable_id' => $user->id]);
    }

    public function test_confirming_an_invalid_code_is_refused(): void
    {
        $user = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(Security::class)->callAction('activate');

        Livewire::test(Security::class)
            ->callAction('confirm', data: ['code' => '000000'])
            ->assertHasActionErrors(['code']);

        $this->assertFalse($user->fresh()->hasEnabledTwoFactor());
    }

    public function test_regenerating_recovery_codes_replaces_them(): void
    {
        $user = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(Security::class)->callAction('activate');
        $user->refresh();
        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);
        Livewire::test(Security::class)->callAction('confirm', data: ['code' => $code]);

        $before = $user->fresh()->two_factor_recovery_codes;

        Livewire::test(Security::class)
            ->assertActionVisible('regenerate')
            ->callAction('regenerate');

        $after = $user->fresh()->two_factor_recovery_codes;
        $this->assertCount(8, $after);
        $this->assertNotEquals($before, $after);
    }

    public function test_disabling_clears_everything(): void
    {
        $user = User::where('email', 'admin@inofarma.co.id')->firstOrFail();

        Livewire::test(Security::class)->callAction('activate');
        $user->refresh();
        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);
        Livewire::test(Security::class)->callAction('confirm', data: ['code' => $code]);

        Livewire::test(Security::class)
            ->assertActionVisible('disable')
            ->callAction('disable');

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertDatabaseHas('audit_logs', ['action' => '2fa_disabled', 'auditable_id' => $user->id]);
    }
}

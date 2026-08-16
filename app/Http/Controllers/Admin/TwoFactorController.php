<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * A staff member's own 2FA setup — enable generates a secret and recovery
 * codes but does not turn enforcement on; `confirm()` is what does, once the
 * user proves the authenticator app is actually scanning it correctly.
 */
class TwoFactorController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $pending = $user->two_factor_secret && ! $user->two_factor_confirmed_at;

        return Inertia::render('Admin/TwoFactor', [
            'enabled' => $user->hasEnabledTwoFactor(),
            'pending' => $pending,
            'qrCodeSvg' => $pending ? (new Google2FA)->getQRCodeInline('Inofarma Admin', $user->email, $user->two_factor_secret) : null,
            'secretKey' => $pending ? $user->two_factor_secret : null,
            'recoveryCodes' => $request->session()->get('two_factor_recovery_codes_reveal'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $codes = $this->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->flash('two_factor_recovery_codes_reveal', $codes);

        return back()->with('success', 'Pindai kode QR dengan aplikasi authenticator Anda, lalu konfirmasi kodenya.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret || ! (new Google2FA)->verifyKey($user->two_factor_secret, $request->string('code'))) {
            throw ValidationException::withMessages(['code' => 'Kode tidak valid.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        AuditLogger::log('2fa_confirmed', $user);

        return back()->with('success', 'Autentikasi dua faktor aktif.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        AuditLogger::log('2fa_disabled', $user);

        return back()->with('success', 'Autentikasi dua faktor dinonaktifkan.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $codes = $this->generateRecoveryCodes();

        $request->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();
        $request->session()->flash('two_factor_recovery_codes_reveal', $codes);

        return back()->with('success', 'Kode pemulihan baru telah dibuat.');
    }

    /**
     * @return list<string>
     */
    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }
}

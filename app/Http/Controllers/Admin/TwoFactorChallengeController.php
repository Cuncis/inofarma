<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * The second step of login for a staff account with 2FA confirmed.
 *
 * `AdminAuthController::login()` verifies the password and, if 2FA is on,
 * stops there — it stores the pending user id in the session but never calls
 * `Auth::login()`. This controller is the only place that actually completes
 * that login, once a valid TOTP code (or a recovery code) is presented.
 */
class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('admin_2fa.user_id')) {
            return redirect()->route('admin.masuk');
        }

        return Inertia::render('Admin/AuthTwoFactorChallenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('admin_2fa.user_id');

        if (! $userId) {
            return redirect()->route('admin.masuk');
        }

        $user = User::findOrFail($userId);

        $request->validate(['code' => ['required', 'string']]);
        $code = str_replace(' ', '', $request->string('code'));

        if ($this->verifyRecoveryCode($user, $code) || $this->verifyTotpCode($user, $code)) {
            $request->session()->forget('admin_2fa.user_id');

            app(AdminAuthController::class)->establishSession($request, $user);

            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang kembali!');
        }

        throw ValidationException::withMessages(['code' => 'Kode tidak valid.']);
    }

    private function verifyTotpCode(User $user, string $code): bool
    {
        return (new Google2FA)->verifyKey($user->two_factor_secret, $code);
    }

    private function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
        ])->save();

        return true;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin sign-in, backed by the real `web` guard and the `users` table.
 *
 * A staff member with 2FA confirmed does not get a session here — `login()`
 * stops short of `Auth::login()` and hands off to
 * `TwoFactorChallengeController` instead, which is the only place that
 * actually establishes the session for that account.
 */
class AdminAuthController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/AuthSignIn');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $request->attemptCredentials();

        if ($user->hasEnabledTwoFactor()) {
            $request->session()->put('admin_2fa.user_id', $user->id);
            $request->session()->put('admin_2fa.remember', $request->boolean('remember'));

            return redirect()->route('admin.dua-faktor');
        }

        $this->establishSession($request, $user);

        return redirect($this->intendedUrl($request))->with('success', 'Selamat datang kembali!');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLogger::log('logout', $request->user());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.masuk');
    }

    public function forgotPassword(): Response
    {
        return Inertia::render('Admin/AuthPassword');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker('users')->sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return back()->with('success', 'Tautan pemulihan telah dikirim ke email Anda.');
    }

    public function showResetPassword(Request $request): Response
    {
        return Inertia::render('Admin/AuthResetPassword', [
            'email' => $request->query('email', ''),
            'token' => $request->route('token'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('admin.masuk')->with('success', 'Kata sandi berhasil diubah. Silakan masuk.');
    }

    public function establishSession(Request $request, User $user): void
    {
        Auth::guard('web')->login($user, $request->session()->pull('admin_2fa.remember', $request->boolean('remember')));

        $request->session()->regenerate();
        $request->session()->put('admin_last_activity', now());

        $user->forceFill(['last_login_at' => now()])->save();

        AuditLogger::log('login', $user);
    }

    private function intendedUrl(Request $request): string
    {
        return $request->session()->pull('admin_intended') ?? route('admin.dashboard');
    }
}

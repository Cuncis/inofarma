<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Cart\CartManager;
use App\Support\CodeSequence;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Customer-facing auth: the `customer` guard, entirely separate from admin.
 * Backs the SignIn/SignUp/VerifyPhone/OtpCode/ForgotPassword/NewPassword
 * screens under `/ui`, which were click-through prototypes before Fase 3.3.
 */
class AuthController extends Controller
{
    /** Bumped whenever `Shop/PrivacyPolicy.jsx`'s substance changes materially (Fase 9.2). */
    private const CONSENT_VERSION = '1.0';

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        // The "Email/No. Telepon" field accepts either — an `@` marks it as
        // an email, otherwise it's looked up as a phone number.
        $field = str_contains($data['email'], '@') ? 'email' : 'phone';

        if (! Auth::guard('customer')->attempt([$field => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages(['email' => 'Email/No. Telepon atau kata sandi salah.']);
        }

        RateLimiter::clear($this->throttleKey($request));

        if (Auth::guard('customer')->user()->status !== 'aktif') {
            Auth::guard('customer')->logout();

            throw ValidationException::withMessages(['email' => 'Akun Anda tidak aktif.']);
        }

        $request->session()->regenerate();

        // A cart built while browsing as a guest (session-backed — see
        // `CartManager`) folds into the customer's saved cart the moment they
        // sign in, so adding to cart before signing in isn't wasted effort.
        (new CartManager($request))->mergeGuestIntoCustomer(Auth::guard('customer')->user());

        $intended = $request->session()->pull('customer_intended');

        return redirect($intended ?? route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();

        return redirect()->route('ui.signin');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/', 'unique:customers,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            // PDP (UU 27/2022) requires an explicit, affirmative action — a
            // pre-ticked or implied checkbox doesn't count as consent. See
            // `Shop/SignUp.jsx` and `Shop/PrivacyPolicy.jsx`.
            'consent' => ['accepted'],
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'consent.accepted' => 'Anda harus menyetujui Kebijakan Privasi untuk mendaftar.',
        ]);

        $customer = Customer::create([
            'code' => CodeSequence::next(Customer::withTrashed(), 'code', 'CUS-'),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'aktif',
            'consent_at' => now(),
            'consent_version' => self::CONSENT_VERSION,
        ]);

        Auth::guard('customer')->login($customer);
        $customer->sendEmailVerificationNotification();

        return redirect()->route('ui.verify-phone');
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $customer = $request->user('customer');

        if ($customer->hasVerifiedEmail()) {
            return back();
        }

        $customer->sendEmailVerificationNotification();

        return back()->with('success', 'Tautan verifikasi baru telah dikirim.');
    }

    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $customer = Customer::withoutGlobalScopes()->findOrFail($id);

        abort_unless(hash_equals($hash, sha1($customer->email)), 403);

        if (! $customer->hasVerifiedEmail()) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('ui.signin')->with('success', 'Email berhasil diverifikasi.');
    }

    /**
     * There is no SMS gateway wired into this project — see the module
     * docblock. In production this becomes a call to a provider like Zenziva
     * or Twilio; for now the code is logged so the flow can be exercised
     * end-to-end in development.
     */
    public function sendPhoneOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);

        $customer = $request->user('customer');
        $customer->forceFill(['phone' => $data['phone']])->save();

        $code = $customer->issuePhoneOtp();

        Log::info("OTP untuk {$customer->phone}: {$code}");

        return redirect()->route('ui.otp-code');
    }

    public function verifyPhoneOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $customer = $request->user('customer');

        if (! $customer->verifyPhoneOtp($data['code'])) {
            throw ValidationException::withMessages(['code' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        return redirect()->route('ui.account-created');
    }

    public function resendPhoneOtp(Request $request): RedirectResponse
    {
        $customer = $request->user('customer');
        $code = $customer->issuePhoneOtp();

        Log::info("OTP untuk {$customer->phone}: {$code}");

        return back()->with('success', 'Kode OTP baru telah dikirim.');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('ui.email-sent');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker('customers')->reset(
            $data,
            function (Customer $customer) use ($data) {
                $customer->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('ui.signin')->with('success', 'Kata sandi berhasil diubah. Silakan masuk.');
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}

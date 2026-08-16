<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Fase 3.1: maksimal 5 percobaan masuk per menit per IP — keyed on the IP
 * alone (not IP+email, unlike the storefront's broker below) so one attacker
 * cannot dodge the limit by cycling through staff email addresses.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Verify credentials without establishing a session yet — the caller
     * decides whether that means a full login or a 2FA challenge first.
     *
     * @throws ValidationException
     */
    public function attemptCredentials(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->string('email'))->first();

        if (! $user || ! Auth::guard('web')->getProvider()->validateCredentials($user, $this->only('password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return 'admin-login:'.$this->ip();
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the admin area, backed by the real `web` guard.
 *
 * Three things beyond a bare `auth` check: a deactivated staff account
 * (`is_active = false`) is logged out immediately even if their session cookie
 * is still valid, a 30-minute idle window auto-locks the session per Fase 3.1,
 * and a staff member with 2FA enabled who hasn't completed the challenge yet
 * (see `TwoFactorChallengeController`) is not considered authenticated here.
 */
class EnsureAdminIsAuthenticated
{
    private const IDLE_TIMEOUT_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if (! $guard->check()) {
            $request->session()->put('admin_intended', $request->fullUrl());

            return redirect()->route('admin.masuk');
        }

        $user = $guard->user();

        if (! $user->is_active) {
            $guard->logout();
            $request->session()->invalidate();

            return redirect()->route('admin.masuk')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        $lastActivity = $request->session()->get('admin_last_activity');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= self::IDLE_TIMEOUT_MINUTES) {
            $guard->logout();
            $request->session()->invalidate();

            return redirect()->route('admin.masuk')->with('error', 'Sesi berakhir karena tidak ada aktivitas selama 30 menit.');
        }

        $request->session()->put('admin_last_activity', now());

        return $next($request);
    }
}

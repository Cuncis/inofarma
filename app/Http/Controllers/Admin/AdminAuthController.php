<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin sign-in.
 *
 * Accepts any email and password on purpose — there is no user table yet. The
 * session user is derived from the email so the topbar and profile screens have
 * a name to show.
 */
class AdminAuthController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if ($request->session()->has('admin_user')) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/AuthSignIn');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $request->session()->regenerate();

        $request->session()->put('admin_user', [
            'name' => Str::of($credentials['email'])
                ->before('@')
                ->replace(['.', '_', '-'], ' ')
                ->title()
                ->value(),
            'email' => $credentials['email'],
        ]);

        $intended = $request->session()->pull('admin_intended');

        return redirect($intended ?? route('admin.dashboard'))
            ->with('success', 'Selamat datang kembali!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_user', 'admin_intended']);
        $request->session()->regenerate();

        return redirect()->route('admin.masuk');
    }

    public function forgotPassword(): Response
    {
        return Inertia::render('Admin/AuthPassword');
    }
}

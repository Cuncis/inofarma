<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for customer-only storefront pages (profile, addresses, order
 * history) — the `customer` guard, entirely separate from the admin `web`
 * guard. See `Customer`'s docblock for why the two are never merged.
 */
class EnsureCustomerIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('customer');

        if (! $guard->check()) {
            $request->session()->put('customer_intended', $request->fullUrl());

            return redirect()->route('ui.signin');
        }

        if ($guard->user()->status !== 'aktif') {
            $guard->logout();

            return redirect()->route('ui.signin')->with('error', 'Akun Anda tidak aktif.');
        }

        return $next($request);
    }
}

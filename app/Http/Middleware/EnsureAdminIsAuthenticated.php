<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the admin area.
 *
 * Prototype-level: the session simply has to carry an `admin_user`, which the
 * login screen puts there for any credentials. There is no user table and no
 * password check yet — swapping in Laravel's real `auth` guard later means
 * replacing this class and the two actions on `AdminAuthController`.
 */
class EnsureAdminIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_user')) {
            // Remember where they were headed so login can return them there.
            $request->session()->put('admin_intended', $request->fullUrl());

            return redirect()->route('admin.masuk');
        }

        return $next($request);
    }
}

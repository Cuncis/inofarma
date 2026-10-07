<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the language picked from the admin panel's profile menu. Indonesian
 * is the default; only the admin panel runs this, so the storefront locale is
 * never touched.
 */
class SetAdminLocale
{
    public const SESSION_KEY = 'admin_locale';

    public const DEFAULT_LOCALE = 'id';

    /** @var list<string> */
    public const LOCALES = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get(self::SESSION_KEY, self::DEFAULT_LOCALE);

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : self::DEFAULT_LOCALE);

        return $next($request);
    }
}

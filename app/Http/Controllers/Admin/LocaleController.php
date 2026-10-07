<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Remember the staff member's language choice for this session. Only the
     * locales the admin panel supports are accepted; anything else is a 404.
     */
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetAdminLocale::LOCALES, true), 404);

        session([SetAdminLocale::SESSION_KEY => $locale]);

        return back();
    }
}

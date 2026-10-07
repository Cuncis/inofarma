<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Inline SVG flags for the admin language switch, drawn at the flags' own 3:2 proportions rather than squeezed into a square icon slot. Flag emoji are not drawn on
 * Windows, so these are real vector flags that look the same everywhere.
 */
class AdminFlags
{
    public static function for(string $locale): HtmlString
    {
        return new HtmlString(match ($locale) {
            'id' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 20" width="24" height="16" aria-hidden="true" style="border-radius:3px;display:block;box-shadow:0 0 0 1px rgb(0 0 0 / .15)"><rect width="30" height="10" fill="#e70011"/><rect y="10" width="30" height="10" fill="#fff"/></svg>',
            default => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 40" width="24" height="16" aria-hidden="true" style="border-radius:3px;display:block;box-shadow:0 0 0 1px rgb(0 0 0 / .15)"><rect width="60" height="40" fill="#012169"/><path d="M0 0l60 40M60 0L0 40" stroke="#fff" stroke-width="8"/><path d="M0 0l60 40M60 0L0 40" stroke="#c8102e" stroke-width="3"/><path d="M30 0v40M0 20h60" stroke="#fff" stroke-width="13"/><path d="M30 0v40M0 20h60" stroke="#c8102e" stroke-width="8"/></svg>',
        });
    }
}

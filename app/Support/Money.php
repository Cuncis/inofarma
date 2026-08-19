<?php

namespace App\Support;

/**
 * The server-side counterpart to `Components/{Shop,Admin}/data.js`'s `money()`
 * — Fase 8 is the first place PHP itself needs to render Rupiah as text
 * (notification bodies), rather than handing a raw integer to Inertia for
 * the frontend to format.
 */
class Money
{
    public static function rupiah(int $amount): string
    {
        return 'Rp'.number_format($amount, 0, ',', '.');
    }
}

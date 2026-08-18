<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * A real customer order number: meaningful (branch + date, at a glance) and
 * not easy to guess (a random suffix), unlike the plain sequential `INO-2451`
 * `CodeSequence` hands out for orders an admin types in by hand.
 */
class OrderNumber
{
    public static function generate(Branch $branch): string
    {
        do {
            $candidate = sprintf(
                'INO-%s-%s-%s',
                $branch->code,
                now()->format('ymd'),
                Str::upper(Str::random(5)),
            );
        } while (Order::withTrashed()->where('number', $candidate)->exists());

        return $candidate;
    }
}

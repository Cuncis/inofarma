<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Next human-readable code in a series: PRD-013, CUS-007, INO-2452.
 *
 * The numbers are computed in PHP rather than in SQL because `MAX(SUBSTRING(…))`
 * is written differently on every driver, and these tables are small enough that
 * it makes no difference.
 *
 * Callers must pass a query that includes soft-deleted rows. A deleted product
 * still occupies its SKU as far as the unique index is concerned, so skipping it
 * would hand out a code that cannot be inserted.
 */
class CodeSequence
{
    /**
     * @param  Builder<*>  $query  scoped with `withTrashed()`
     * @param  string  $prefix  including the separator, e.g. 'PRD-'
     * @param  int  $pad  zero-padding width; 0 leaves the number unpadded
     * @param  int  $floor  number to continue from when the table is empty
     */
    public static function next(
        Builder $query,
        string $column,
        string $prefix,
        int $pad = 3,
        int $floor = 0,
    ): string {
        $highest = $query->pluck($column)
            ->filter(fn (?string $code) => $code !== null && str_starts_with($code, $prefix))
            ->map(fn (string $code) => (int) Str::after($code, $prefix))
            ->max();

        $next = max((int) $highest, $floor) + 1;

        return $prefix.($pad > 0
            ? str_pad((string) $next, $pad, '0', STR_PAD_LEFT)
            : (string) $next);
    }
}

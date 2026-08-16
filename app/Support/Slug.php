<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Slugs are unique columns, so two products called "Vitamin C" would collide.
 * This appends -2, -3… until the slug is free, skipping the record being edited.
 */
class Slug
{
    /**
     * @param  Builder<*>  $query  scoped with `withTrashed()`; a soft-deleted row
     *                             still holds its slug against the unique index
     */
    public static function unique(
        Builder $query,
        string $value,
        string $column = 'slug',
        ?int $ignoreId = null,
    ): string {
        $base = Str::slug($value);
        $candidate = $base;
        $suffix = 2;

        while (self::taken($query, $column, $candidate, $ignoreId)) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @param  Builder<*>  $query
     */
    private static function taken(Builder $query, string $column, string $value, ?int $ignoreId): bool
    {
        return (clone $query)
            ->where($column, $value)
            ->when($ignoreId, fn (Builder $inner) => $inner->whereKeyNot($ignoreId))
            ->exists();
    }
}

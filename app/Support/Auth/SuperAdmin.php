<?php

namespace App\Support\Auth;

use Illuminate\Support\Facades\Auth;

/**
 * Things reserved for the Super Admin role itself, which no permission in
 * Hak Akses can grant to another role.
 */
class SuperAdmin
{
    public const ROLE = 'Super Admin';

    /** An SVG can carry a script that runs when its URL is opened on this domain. */
    public const SVG_TYPE = 'image/svg+xml';

    public static function check(): bool
    {
        return (bool) Auth::guard('web')->user()?->hasRole(self::ROLE);
    }

    /**
     * The upload types the signed-in staff member may use: SVG is dropped for
     * everyone but Super Admin. SVGs already uploaded stay where they are.
     *
     * @param  list<string>  $types
     * @return list<string>
     */
    public static function uploadTypes(array $types): array
    {
        return self::check() ? $types : array_values(array_diff($types, [self::SVG_TYPE]));
    }
}

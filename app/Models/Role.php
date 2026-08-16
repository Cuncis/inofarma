<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Adds `description` on top of spatie/laravel-permission's stock Role model —
 * see the migration that added the column for why.
 */
class Role extends SpatieRole
{
    protected $fillable = ['name', 'description', 'guard_name'];
}

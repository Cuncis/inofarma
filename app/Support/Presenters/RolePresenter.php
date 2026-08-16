<?php

namespace App\Support\Presenters;

use App\Models\Role;

class RolePresenter
{
    /**
     * @param  iterable<Role>  $roles
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $roles): array
    {
        return collect($roles)->map(fn (Role $role) => self::toArray($role))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Role $role): array
    {
        return [
            'name' => $role->name,
            'description' => $role->description,
            'users' => $role->users_count ?? $role->users()->count(),
            'permissions' => $role->permissions_count ?? $role->permissions()->count(),
            'grantedPermissions' => $role->permissions->pluck('name')->values()->all(),
        ];
    }
}

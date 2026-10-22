<?php

namespace App\Support\Auth;

use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Maps every Filament authorization check on a resource to one of the
 * `Module:Ability` permissions from `PermissionCatalog`.
 *
 * There are no Policy classes in this app, and Filament allows any action it
 * cannot find a policy for. Without this, a resource that only overrides
 * `canViewAny()` still lets anyone who can open it create, edit, delete and
 * restore records. An action missing from `permissions()` is denied (Super
 * Admin passes everything through `Gate::before()`). An action mapped to
 * `SuperAdmin::ROLE` is for that role alone, whatever Hak Akses grants.
 */
trait AuthorizesByPermission
{
    /**
     * The permission each Filament action needs. `viewAny` also gates `view`;
     * `create`, `update` and `delete` cover their bulk, force and restore
     * variants.
     *
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    abstract protected static function permissions(): array;

    public static function getAuthorizationResponse(string $action, ?Model $record = null): Response
    {
        $permission = static::permissionFor($action);

        $allowed = match ($permission) {
            null => false,
            SuperAdmin::ROLE => SuperAdmin::check(),
            default => (bool) Auth::guard('web')->user()?->can($permission),
        };

        return $allowed ? Response::allow() : Response::deny();
    }

    public static function permissionFor(string $action): ?string
    {
        $ability = match ($action) {
            'viewAny', 'view' => 'viewAny',
            'create', 'replicate' => 'create',
            'update', 'reorder' => 'update',
            'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny' => 'delete',
            default => null,
        };

        return $ability ? (static::permissions()[$ability] ?? null) : null;
    }
}

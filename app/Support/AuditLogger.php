<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Writes to `audit_logs` — who, what, when, from which IP, at which branch.
 *
 * Not wired into every admin controller yet; that would mean touching every
 * CRUD action in the app for one session. It currently covers the
 * security-sensitive actions Fase 3 asks for explicitly: sign-in/out, 2FA
 * changes, role/permission and staff changes, and stock mutations (which
 * already ran through `StockAllocator`/`StockAdjuster` and previously had
 * nowhere real to attribute a user id to). Extending it to product/order/etc.
 * CRUD is a mechanical follow-up, not a design question — see
 * .ai/rules/support.md.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $actor = null,
        ?int $branchId = null,
    ): AuditLog {
        $actor ??= Auth::guard('web')->user();

        return AuditLog::create([
            'user_id' => $actor?->id,
            'branch_id' => $branchId ?? $actor?->branch_id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : User::class,
            'auditable_id' => $auditable?->getKey() ?? $actor?->id ?? 0,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

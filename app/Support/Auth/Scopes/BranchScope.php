<?php

namespace App\Support\Auth\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Confines a query to the signed-in staff member's own branch.
 *
 * This is the query-layer half of Fase 3.2's "cakupan cabang" — a Kasir at
 * Cabang Otista cannot see another branch's rows no matter what the
 * controller does or doesn't check, because the row never leaves the
 * database. It is a no-op for a central user (`branch_id` null) and for
 * anything running outside an authenticated `web` request — console
 * commands, queued jobs, other guards — since there is no branch to confine
 * to in those contexts.
 */
class BranchScope implements Scope
{
    public function __construct(private readonly string $column = 'branch_id') {}

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::guard('web')->user();

        if (! $user || $user->branch_id === null) {
            return;
        }

        $builder->where($model->qualifyColumn($this->column), $user->branch_id);
    }
}

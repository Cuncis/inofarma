<?php

namespace App\Support\Auth\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * A `StockTransfer` has no single `branch_id` — it belongs to whichever
 * branch is shipping or receiving. A scoped user should see a transfer either
 * way it touches their branch, not just outbound ones.
 */
class TransferBranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::guard('web')->user();

        if (! $user || $user->branch_id === null) {
            return;
        }

        $builder->where(function (Builder $query) use ($user) {
            $query->where('from_branch_id', $user->branch_id)
                ->orWhere('to_branch_id', $user->branch_id);
        });
    }
}

<?php

namespace App\Support\Auth\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * A customer doesn't belong to one branch, but a scoped staff member should
 * still only see the customers who have actually ordered from their branch —
 * "pelanggan Cabang Otista" in the roadmap's wording means "people who've
 * shopped there," not a foreign key on the customer row itself.
 */
class CustomerBranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::guard('web')->user();

        if (! $user || $user->branch_id === null) {
            return;
        }

        $builder->whereHas('orders', function (Builder $query) use ($user) {
            $query->where('branch_id', $user->branch_id);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A discount code, optionally scoped to a set of branches.
 *
 * No branches attached means valid everywhere — the same "empty means all"
 * convention `users.branch_id IS NULL` uses for central staff.
 */
class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'type', 'value', 'minimum_purchase', 'quota', 'used_count',
        'starts_at', 'expires_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'minimum_purchase' => 'integer',
            'quota' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'coupon_branch');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function getAppliesToAllBranchesAttribute(): bool
    {
        return $this->branches->isEmpty();
    }

    public function appliesToBranch(Branch|int $branch): bool
    {
        if ($this->branches->isEmpty()) {
            return true;
        }

        $id = $branch instanceof Branch ? $branch->id : $branch;

        return $this->branches->contains('id', $id);
    }

    public function getIsExhaustedAttribute(): bool
    {
        return $this->quota !== null && $this->used_count >= $this->quota;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * The discount a subtotal earns from this coupon, in rupiah — never more
     * than the subtotal itself. "Gratis Ongkir" carries no product discount;
     * it is applied to `shipping_total` by the caller instead.
     */
    public function discountFor(int $subtotal): int
    {
        return match ($this->type) {
            'persentase' => (int) min($subtotal, round($subtotal * $this->value / 100)),
            'nominal' => min($subtotal, $this->value),
            default => 0,
        };
    }

    public function getIsFreeShippingAttribute(): bool
    {
        return $this->type === 'ongkir gratis';
    }
}

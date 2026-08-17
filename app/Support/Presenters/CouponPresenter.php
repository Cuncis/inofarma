<?php

namespace App\Support\Presenters;

use App\Models\Coupon;
use App\Support\AdminOptions;

/**
 * `id` is the code — `/admin/kupon/HEMAT15`. `status` shown to the admin
 * folds in "Habis" (quota reached) on top of the stored aktif/nonaktif value,
 * the same way `AdminOptions::stockLabel()` layers availability over a
 * product's own status.
 */
class CouponPresenter
{
    /**
     * @param  iterable<Coupon>  $coupons
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $coupons): array
    {
        return collect($coupons)->map(fn (Coupon $coupon) => self::toArray($coupon))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Coupon $coupon): array
    {
        return [
            'id' => $coupon->code,
            'code' => $coupon->code,
            'type' => AdminOptions::toLabel(AdminOptions::COUPON_TYPES, $coupon->type),
            'value' => $coupon->value,
            'minimumPurchase' => $coupon->minimum_purchase,
            'quota' => $coupon->quota,
            'usedCount' => $coupon->used_count,
            'startsAt' => $coupon->starts_at?->format('Y-m-d'),
            'expiresAt' => $coupon->expires_at?->format('Y-m-d'),
            'status' => self::status($coupon),
            'appliesToAllBranches' => $coupon->applies_to_all_branches,
            'branches' => $coupon->branches->pluck('code')->all(),
            'branchNames' => $coupon->branches->pluck('name')->all(),
        ];
    }

    private static function status(Coupon $coupon): string
    {
        if ($coupon->is_exhausted) {
            return 'Habis';
        }

        if ($coupon->is_expired) {
            return 'Kedaluwarsa';
        }

        return AdminOptions::toLabel(AdminOptions::COUPON_STATUSES, $coupon->status);
    }
}

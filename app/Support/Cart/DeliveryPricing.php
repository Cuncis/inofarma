<?php

namespace App\Support\Cart;

use App\Models\Branch;
use App\Models\CustomerAddress;

/**
 * The branch's own delivery-radius policy check — distinct from, and
 * checked before, whatever couriers Biteship's `rates()` actually returns
 * (Fase 7, `App\Support\Shipping\ShippingQuoteService`). A cabang may simply
 * not want to deliver past a certain distance even if a courier technically
 * could reach further; this is that business rule, not a pricing engine.
 * Distance is a real Haversine calculation between the branch and the
 * delivery address, same formula `Branch::scopeNearest()` runs in SQL.
 */
class DeliveryPricing
{
    public static function distanceKm(Branch $branch, CustomerAddress $address): ?float
    {
        if ($branch->latitude === null || $branch->longitude === null
            || $address->latitude === null || $address->longitude === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $lat1 = deg2rad((float) $branch->latitude);
        $lat2 = deg2rad((float) $address->latitude);
        $deltaLat = deg2rad((float) $address->latitude - (float) $branch->latitude);
        $deltaLng = deg2rad((float) $address->longitude - (float) $branch->longitude);

        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Unknown distance (missing coordinates) is treated as within radius — nothing to reject against. */
    public static function isWithinRadius(Branch $branch, CustomerAddress $address): bool
    {
        $distance = self::distanceKm($branch, $address);

        if ($distance === null || ! $branch->delivery_radius_km) {
            return true;
        }

        return $distance <= $branch->delivery_radius_km;
    }
}

<?php

namespace App\Support\Cart;

use App\Models\Branch;
use App\Models\CustomerAddress;

/**
 * A stand-in for real courier pricing until Fase 7 wires up RajaOngkir/Biteship
 * with each branch's own origin coordinates. Distance is still computed for
 * real — Haversine between the branch and the delivery address, same formula
 * `Branch::scopeNearest()` runs in SQL — so the radius check is genuine even
 * though the fee itself is a flat placeholder.
 */
class DeliveryPricing
{
    /** Rupiah, regardless of distance, until Fase 7 replaces this with a real courier quote. */
    public const FLAT_FEE = 10000;

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

    public static function fee(bool $freeShipping): int
    {
        return $freeShipping ? 0 : self::FLAT_FEE;
    }
}

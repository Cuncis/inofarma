<?php

namespace App\Support\Cart;

use App\Models\CustomerAddress;
use App\Support\Geocoding\PostalCodeGeocoder;

/**
 * An address typed straight into the checkout form, before (or without) being
 * saved. Courier quotes and the delivery radius need a map point, and a shopper
 * who filled in the region but never pinned a spot still has a postal code, so
 * the point is worked out from that (approximate, but enough to price a ride).
 */
class DraftAddress
{
    /**
     * @param  array<string, mixed>  $fields  addressLine, kelurahan, kecamatan, kota, provinsi, postalCode, latitude, longitude, plus recipient name and phone
     */
    public static function make(array $fields): CustomerAddress
    {
        $latitude = $fields['latitude'] ?? null;
        $longitude = $fields['longitude'] ?? null;
        $postalCode = $fields['postalCode'] ?? null;

        if (($latitude === null || $longitude === null) && $postalCode) {
            $point = (new PostalCodeGeocoder)->find((string) $postalCode);
            $latitude = $point['lat'] ?? null;
            $longitude = $point['lng'] ?? null;
        }

        return new CustomerAddress([
            'label' => 'Rumah',
            'recipient_name' => $fields['recipientName'] ?? null,
            'phone' => $fields['recipientPhone'] ?? null,
            'address_line' => $fields['addressLine'] ?? null,
            'kelurahan' => $fields['kelurahan'] ?? null,
            'kecamatan' => $fields['kecamatan'] ?? null,
            'kota' => $fields['kota'] ?? null,
            'provinsi' => $fields['provinsi'] ?? null,
            'postal_code' => $postalCode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /** @param  array<string, mixed>  $fields */
    public static function isComplete(array $fields): bool
    {
        return filled($fields['addressLine'] ?? null) && filled($fields['kota'] ?? null) && filled($fields['provinsi'] ?? null);
    }
}

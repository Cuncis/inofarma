<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Where a shopper is, well enough to sort branches by distance.
 *
 * Two ways in: the browser's geolocation API (a lat/lng pair) or the manual
 * fallback picker (provinsi → kota → kecamatan, for the many people who decline
 * the location prompt). Either is saved to the session so it isn't asked for
 * again on the next visit — see `LocationController::store()`.
 */
class LocationPreference
{
    private const SESSION_KEY = 'shop_location';

    /**
     * The coordinates to sort branches from, from (in order of preference) the
     * request's own query string, the saved session value, or none at all.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function coordinates(Request $request): ?array
    {
        $lat = $request->query('lat') ?? $request->session()->get(self::SESSION_KEY.'.lat');
        $lng = $request->query('lng') ?? $request->session()->get(self::SESSION_KEY.'.lng');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    /**
     * The manual fallback area, when no coordinates are available.
     *
     * @return array{provinsi: ?string, kota: ?string, kecamatan: ?string}|null
     */
    public static function area(Request $request): ?array
    {
        $stored = $request->session()->get(self::SESSION_KEY);

        $provinsi = $request->query('provinsi') ?? $stored['provinsi'] ?? null;
        $kota = $request->query('kota') ?? $stored['kota'] ?? null;

        if (! $provinsi && ! $kota) {
            return null;
        }

        return [
            'provinsi' => $provinsi,
            'kota' => $kota,
            'kecamatan' => $request->query('kecamatan') ?? $stored['kecamatan'] ?? null,
        ];
    }

    public static function remember(Request $request, array $location): void
    {
        $request->session()->put(self::SESSION_KEY, $location);
    }

    public static function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}

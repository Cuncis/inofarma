<?php

namespace App\Support\Geocoding;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Turns an Indonesian postal code into a point on the map, so "Kode pos" in the
 * pickup location popup can sort branches by distance. Uses Nominatim
 * (OpenStreetMap), free and keyless like the `cabang:geocode` command; its
 * usage policy asks for an identifying User-Agent and at most one request a
 * second, so answers are cached for a month (a postal code does not move) and
 * the route is throttled.
 */
class PostalCodeGeocoder
{
    /**
     * @return array{lat: float, lng: float, label: string}|null null when the code is unknown or the service is unreachable
     */
    public function find(string $postalCode): ?array
    {
        if (! preg_match('/^\d{5}$/', $postalCode)) {
            return null;
        }

        return Cache::remember("geocode:kodepos:{$postalCode}", now()->addMonth(), function () use ($postalCode) {
            try {
                $response = Http::withHeaders(['User-Agent' => 'Inofarma-Geocoder/1.0 (storefront pickup locations)'])
                    ->timeout(8)
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'format' => 'json',
                        'limit' => 1,
                        'postalcode' => $postalCode,
                        'countrycodes' => 'id',
                    ]);
            } catch (ConnectionException) {
                return null;
            }

            $result = $response->successful() ? $response->json(0) : null;

            return $result
                ? ['lat' => (float) $result['lat'], 'lng' => (float) $result['lon'], 'label' => (string) ($result['display_name'] ?? $postalCode)]
                : null;
        });
    }
}

<?php

namespace App\Support\Presenters;

use App\Models\Branch;
use App\Support\AdminOptions;

/**
 * Turns a `Branch` into the shape the admin screens expect.
 *
 * `id` is the branch code (`CB-001`), the admin route key. Coordinates come
 * back as nullable floats — a branch waiting on geocoding shows that plainly
 * rather than defaulting to `0,0`, which is a real point in the Gulf of Guinea.
 */
class BranchPresenter
{
    /**
     * @param  iterable<Branch>  $branches
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $branches): array
    {
        return collect($branches)->map(fn (Branch $branch) => self::toArray($branch))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Branch $branch): array
    {
        return [
            'id' => $branch->code,
            'name' => $branch->name,
            'slug' => $branch->slug,
            'addressLine' => $branch->address_line,
            'kelurahan' => $branch->kelurahan,
            'kecamatan' => $branch->kecamatan,
            'kota' => $branch->kota,
            'provinsi' => $branch->provinsi,
            'postalCode' => $branch->postal_code,
            'fullAddress' => $branch->full_address,
            'latitude' => $branch->latitude !== null ? (float) $branch->latitude : null,
            'longitude' => $branch->longitude !== null ? (float) $branch->longitude : null,
            'mapsUrl' => $branch->maps_url,
            'phone' => $branch->phone,
            'whatsapp' => $branch->whatsapp,
            'siaNumber' => $branch->sia_number,
            'apjName' => $branch->apj_name,
            'apjSipaNumber' => $branch->apj_sipa_number,
            'operatingHours' => $branch->operating_hours,
            'supportsDelivery' => $branch->supports_delivery,
            'supportsPickup' => $branch->supports_pickup,
            'deliveryRadiusKm' => $branch->delivery_radius_km,
            'status' => AdminOptions::toLabel(AdminOptions::BRANCH_STATUSES, $branch->status),
            'isOpenNow' => $branch->is_open_now,
            'stockCount' => (int) ($branch->stocks_count ?? $branch->stocks()->count()),
            'staffCount' => (int) ($branch->staff_count ?? $branch->staff()->count()),
        ];
    }

    /**
     * Slim options for a dropdown — every screen that only needs to name a
     * branch, not describe it.
     *
     * @param  iterable<Branch>  $branches
     * @return list<array{id: string, name: string}>
     */
    public static function options(iterable $branches): array
    {
        return collect($branches)
            ->map(fn (Branch $branch) => ['id' => $branch->code, 'name' => $branch->name])
            ->values()
            ->all();
    }
}

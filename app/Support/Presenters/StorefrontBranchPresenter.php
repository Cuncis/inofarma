<?php

namespace App\Support\Presenters;

use App\Models\Branch;
use App\Models\Product;
use App\Support\HomePageContent;
use App\Support\SiteFooterContent;
use Illuminate\Support\Collection;

/**
 * Turns branches into what a shopper sees: distance, open/closed, and whether
 * they can actually pick this branch — not what an administrator sees.
 */
class StorefrontBranchPresenter
{
    /**
     * Every active branch for the branch locator API, no product involved.
     *
     * @param  iterable<Branch>  $branches
     * @return list<array<string, mixed>>
     */
    public static function list(iterable $branches): array
    {
        return collect($branches)->map(fn (Branch $branch) => [
            ...self::base($branch),
            'addressLine' => $branch->address_line,
            'kecamatan' => $branch->kecamatan,
            'fullAddress' => $branch->full_address,
            'phone' => $branch->phone,
            'whatsapp' => $branch->whatsapp,
            'todaysHours' => $branch->todays_hours,
            'siaNumber' => $branch->sia_number,
            'apjName' => $branch->apj_name,
            'apjSipaNumber' => $branch->apj_sipa_number,
        ])->values()->all();
    }

    /**
     * The photo cards of "Apotek Inofarma Terdekat" on the homepage, and the
     * "Apotek Terdekat Lainnya" list under an apotek's own page.
     *
     * @param  iterable<Branch>  $branches  each with a `photo_path`
     * @return list<array{slug: string, name: string, photoUrl: string, fullAddress: string, apjName: ?string}>
     */
    public static function cards(iterable $branches): array
    {
        return collect($branches)->map(fn (Branch $branch) => [
            'slug' => $branch->slug,
            'name' => $branch->name,
            'photoUrl' => HomePageContent::imageUrl($branch->photo_path),
            'fullAddress' => $branch->full_address,
            'apjName' => $branch->apj_name,
        ])->values()->all();
    }

    /**
     * One apotek's own page. Fase 9.1: it must show its SIA and its APJ's
     * name + SIPA number, a compliance/trust requirement, not just contact info.
     *
     * @return array<string, mixed>
     */
    public static function page(Branch $branch): array
    {
        return [
            'slug' => $branch->slug,
            'name' => $branch->name,
            'status' => $branch->status,
            'photoUrl' => $branch->photo_path ? HomePageContent::imageUrl($branch->photo_path) : null,
            'fullAddress' => $branch->full_address,
            'siaNumber' => $branch->sia_number,
            'apjName' => $branch->apj_name,
            'apjSipaNumber' => $branch->apj_sipa_number,
            'practiceSchedule' => $branch->practice_schedule,
            'storeSchedule' => $branch->store_schedule,
            'contactUrl' => self::contactUrl($branch),
            'mapsUrl' => $branch->maps_url,
        ];
    }

    /**
     * "Hubungi Kami": the branch's WhatsApp, else its phone, else the
     * company's customer service number (on WhatsApp).
     */
    private static function contactUrl(Branch $branch): ?string
    {
        if ($branch->whatsapp) {
            return self::whatsappUrl($branch->whatsapp);
        }

        if ($branch->phone) {
            return 'tel:'.preg_replace('/[^\d+]/', '', $branch->phone);
        }

        $companyPhone = SiteFooterContent::company()['phone'];

        return $companyPhone !== '' ? self::whatsappUrl($companyPhone) : null;
    }

    /** `0812-1401-8964` and `+62 812 1401 8964` both become `https://wa.me/6281214018964`. */
    private static function whatsappUrl(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        return 'https://wa.me/'.(str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits);
    }

    /**
     * Branches carrying one product — the picker on the product detail page.
     *
     * A branch with no stock is still listed (a shopper needs to know it
     * exists) but marked `selectable: false` rather than left out, and a
     * closed branch is listed but not selectable for pickup today.
     *
     * @param  iterable<Branch>  $branches  each with `stocks` eager-loaded and
     *                                      filtered to this one product
     * @return list<array<string, mixed>>
     */
    public static function forProduct(iterable $branches): array
    {
        return collect($branches)->map(function (Branch $branch) {
            $stock = $branch->stocks->first();
            $available = $stock ? max($stock->quantity - $stock->reserved_quantity, 0) : 0;

            return [
                ...self::base($branch),
                'fullAddress' => $branch->full_address,
                'available' => $available,
                'selectable' => $available > 0 && $branch->status === 'aktif',
            ];
        })->values()->all();
    }

    /**
     * Pickup branches at checkout: every open branch can collect the order,
     * whatever its stock. All branches belong to one company, so an empty
     * shelf at the chosen branch is the company's to sort out, not the
     * shopper's to be blocked by.
     *
     * @param  iterable<Branch>  $branches
     * @return list<array<string, mixed>>
     */
    public static function forPickup(iterable $branches): array
    {
        return collect($branches)->map(fn (Branch $branch) => [
            ...self::base($branch),
            'fullAddress' => $branch->full_address,
            'available' => 1,
            'selectable' => $branch->status === 'aktif',
        ])->sort(fn (array $a, array $b) => [! $a['selectable'], $a['distanceKm'] ?? INF, $a['name']] <=> [! $b['selectable'], $b['distanceKm'] ?? INF, $b['name']])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function base(Branch $branch): array
    {
        $distance = $branch->getAttribute('distance_km');

        return [
            'id' => $branch->code,
            'name' => $branch->name,
            'kota' => $branch->kota,
            'distanceKm' => $distance !== null ? round((float) $distance, 1) : null,
            'isOpenNow' => $branch->is_open_now,
            'status' => $branch->status,
            'supportsDelivery' => $branch->supports_delivery,
            'supportsPickup' => $branch->supports_pickup,
            'deliveryRadiusKm' => $branch->delivery_radius_km,
            'mapsUrl' => $branch->maps_url,
        ];
    }

    /**
     * Sort branches nearest-first when they carry a distance, otherwise leave
     * the incoming (alphabetical/kota) order alone.
     *
     * @param  Collection<int, array<string, mixed>>|list<array<string, mixed>>  $branches
     * @return list<array<string, mixed>>
     */
    public static function sortByDistance(iterable $branches): array
    {
        return collect($branches)
            ->sortBy(fn (array $branch) => $branch['distanceKm'] ?? INF)
            ->values()
            ->all();
    }
}

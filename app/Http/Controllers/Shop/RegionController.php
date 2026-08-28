<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The cascading Provinsi/Kota/Kecamatan/Kelurahan dropdowns on
 * `Shop/AddNewAddress.jsx` — a plain JSON endpoint, same shape as
 * `ShippingController::rates()`, reading from `regions` (see
 * `regions:import`). Provinsi (level 1) is the only level with no parent to
 * filter by; every other level requires `parent`.
 */
class RegionController extends Controller
{
    public function children(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent' => ['nullable', 'string', 'max:13'],
        ]);

        $query = Region::query()->orderBy('name');

        if (empty($data['parent'])) {
            $query->whereNull('parent_code');
        } else {
            $query->where('parent_code', $data['parent']);
        }

        return response()->json([
            'options' => $query->get(['code', 'name', 'postal_code'])->map(fn (Region $region) => [
                'code' => $region->code,
                'name' => $region->name,
                'postalCode' => $region->postal_code,
            ]),
        ]);
    }
}

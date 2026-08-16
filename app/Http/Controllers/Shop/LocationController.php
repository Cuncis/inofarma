<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\LocationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Remembers where a shopper is (or which area they picked) so the branch
 * picker doesn't ask again on every visit.
 */
class LocationController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'provinsi' => ['nullable', 'string', 'max:80'],
            'kota' => ['nullable', 'string', 'max:80', 'required_with:provinsi'],
            'kecamatan' => ['nullable', 'string', 'max:80'],
        ]);

        LocationPreference::remember($request, array_filter($data, fn ($value) => $value !== null));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        LocationPreference::forget($request);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }
}

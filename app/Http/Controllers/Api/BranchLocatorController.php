<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Support\Geocoding\PostalCodeGeocoder;
use App\Support\LocationPreference;
use App\Support\Presenters\StorefrontBranchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints the storefront calls after the page has already loaded — once
 * the browser's geolocation prompt resolves (accepted or declined), the branch
 * list re-sorts without a full page reload.
 */
class BranchLocatorController extends Controller
{
    /**
     * Every active branch, nearest first when coordinates are known.
     */
    public function nearest(Request $request): JsonResponse
    {
        $coordinates = LocationPreference::coordinates($request);

        $query = Branch::query()->active();

        if ($coordinates) {
            $query->nearest($coordinates['lat'], $coordinates['lng']);
        } else {
            $query->orderBy('kota')->orderBy('name');
        }

        return response()->json([
            'branches' => StorefrontBranchPresenter::list($query->get()),
        ]);
    }

    /**
     * Looks a postal code up and answers with its point, for the pickup
     * location popup. It only answers; saving the point as the shopper's
     * location is the existing `POST /lokasi`.
     */
    public function postalCode(Request $request, PostalCodeGeocoder $geocoder): JsonResponse
    {
        $code = (string) $request->validate(['kode' => ['required', 'digits:5']])['kode'];

        $point = $geocoder->find($code);

        abort_if($point === null, 404, 'Kode pos tidak ditemukan.');

        return response()->json($point);
    }

    /**
     * Every active branch carrying a product, nearest first, sorted so a
     * shopper sees the closest branch that actually has it before one further
     * away with nothing on the shelf.
     */
    public function forProduct(Request $request, string $product): JsonResponse
    {
        $productModel = Product::where('sku', $product)
            ->firstOr(fn () => abort(404, 'Produk tidak ditemukan.'));

        $coordinates = LocationPreference::coordinates($request);

        $query = Branch::query()
            ->active()
            ->with(['stocks' => fn ($stocks) => $stocks->where('product_id', $productModel->id)]);

        // Every branch stays in the list; one without coordinates just has no distance.
        if ($coordinates) {
            $query->withDistanceFrom($coordinates['lat'], $coordinates['lng']);
        }

        $query->orderBy('kota')->orderBy('name');

        $branches = StorefrontBranchPresenter::forProduct($query->get());

        // In stock and closer beats out of stock and closer — a shopper who
        // can see the product at all wants to know where they can buy it.
        // `distanceKm` is null with no coordinates, which sorts first; that's
        // fine, every row is null together and the tiebreak below still holds.
        $ordered = collect($branches)
            ->sort(fn (array $a, array $b) => [! $a['selectable'], $a['distanceKm'] ?? INF] <=> [! $b['selectable'], $b['distanceKm'] ?? INF])
            ->values()
            ->all();

        return response()->json(['branches' => $ordered]);
    }
}

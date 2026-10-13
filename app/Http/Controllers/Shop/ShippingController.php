<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Cart\CartManager;
use App\Support\Cart\DraftAddress;
use App\Support\Shipping\ShippingQuoteService;
use App\Support\Shipping\ShippingUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Live courier quotes for the checkout screen's "antar" picker — a plain
 * JSON endpoint rather than an Inertia page, called from `Shop/Checkout.tsx`
 * once a branch and address are both known. `CheckoutController::store()`
 * never trusts whatever price this returned; it re-quotes on its own before
 * writing an order's money columns.
 */
class ShippingController extends Controller
{
    public function rates(Request $request, CartManager $cart): JsonResponse
    {
        $data = $cart->current();

        // A saved address wins; otherwise the address still being typed into
        // the checkout form arrives as query fields.
        $address = $data['address'] ?? ($request->filled('postalCode') || $request->filled('latitude')
            ? DraftAddress::make($request->validate([
                'postalCode' => ['nullable', 'string', 'max:10'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ]))
            : null);

        if (! $data['branch'] || ! $address) {
            return response()->json(['options' => []]);
        }

        $origin = CartManager::deliveryBranch($data['branch'], $data['lines']);

        if (! $origin) {
            return response()->json(['options' => []]);
        }

        try {
            $options = ShippingQuoteService::make()->quote($origin, $address, $data['lines']);
        } catch (ShippingUnavailableException $exception) {
            report($exception);

            return response()->json(['options' => [], 'error' => $exception->customerMessage], 422);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['options' => [], 'error' => 'Gagal memuat ongkos kirim. Coba lagi.'], 502);
        }

        return response()->json(['options' => $options]);
    }
}

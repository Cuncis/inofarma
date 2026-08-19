<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Cart\CartManager;
use App\Support\Shipping\ShippingQuoteService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Live courier quotes for the checkout screen's "antar" picker — a plain
 * JSON endpoint rather than an Inertia page, called from `Shop/Checkout.jsx`
 * once a branch and address are both known. `CheckoutController::store()`
 * never trusts whatever price this returned; it re-quotes on its own before
 * writing an order's money columns.
 */
class ShippingController extends Controller
{
    public function rates(CartManager $cart): JsonResponse
    {
        $data = $cart->current();

        if (! $data['branch'] || ! $data['address']) {
            return response()->json(['options' => []]);
        }

        try {
            $options = ShippingQuoteService::make()->quote($data['branch'], $data['address'], $data['lines']);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['options' => [], 'error' => 'Gagal memuat ongkos kirim. Coba lagi.'], 502);
        }

        return response()->json(['options' => $options]);
    }
}

<?php

namespace App\Support\Presenters;

use App\Models\Branch;
use App\Models\Coupon;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Support\AdminOptions;

/**
 * Turns `CartManager::current()`'s array into what the Cart and Checkout
 * screens render. Prices are always computed live — from the branch's
 * `price_override` when one exists, otherwise the national price — a cart
 * never freezes a price the way an order's line items do.
 */
class CartPresenter
{
    /**
     * @param  array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}  $cart
     * @return array<string, mixed>
     */
    public static function toArray(array $cart): array
    {
        $branch = $cart['branch'];
        $items = collect($cart['lines'])->map(fn (array $line) => self::item($line['product'], $line['quantity'], $branch))->all();
        $subtotal = (int) collect($items)->sum('lineTotal');
        $discount = $cart['coupon'] ? min($subtotal, $cart['coupon']->discountFor($subtotal)) : 0;

        return [
            'branch' => $branch ? self::branch($branch) : null,
            'address' => $cart['address'] ? CustomerAddressPresenter::toArray($cart['address']) : null,
            'items' => $items,
            'itemCount' => (int) collect($items)->sum('quantity'),
            'subtotal' => $subtotal,
            'coupon' => $cart['coupon'] ? [
                'code' => $cart['coupon']->code,
                'type' => AdminOptions::toLabel(AdminOptions::COUPON_TYPES, $cart['coupon']->type),
                'freeShipping' => $cart['coupon']->is_free_shipping,
                'discount' => $discount,
            ] : null,
            'discount' => $discount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function item(Product $product, int $quantity, ?Branch $branch): array
    {
        $stock = $branch ? $product->stockAt($branch) : null;
        $price = $stock?->effective_price ?? $product->price;
        $available = $stock ? max($stock->quantity - $stock->reserved_quantity, 0) : 0;

        return [
            'sku' => $product->sku,
            'name' => $product->name,
            'image' => $product->image_path,
            'unit' => $product->unit,
            'unitPrice' => $price,
            'quantity' => $quantity,
            'lineTotal' => $price * $quantity,
            'available' => $available,
            'maxQtyPerOrder' => $product->max_qty_per_order,
            'needsWarningLabel' => $product->needs_warning_label,
            'prescription' => $product->requires_prescription,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function branch(Branch $branch): array
    {
        return [
            'id' => $branch->code,
            'name' => $branch->name,
            'kota' => $branch->kota,
            'addressLine' => $branch->address_line,
            'fullAddress' => $branch->full_address,
            'phone' => $branch->phone,
            'whatsapp' => $branch->whatsapp,
            'mapsUrl' => $branch->maps_url,
            'supportsDelivery' => $branch->supports_delivery,
            'supportsPickup' => $branch->supports_pickup,
            'deliveryRadiusKm' => $branch->delivery_radius_km,
            'isOpenNow' => $branch->is_open_now,
            'operatingHours' => $branch->operating_hours,
        ];
    }
}

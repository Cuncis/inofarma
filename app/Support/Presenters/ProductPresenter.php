<?php

namespace App\Support\Presenters;

use App\Models\Product;
use App\Support\AdminOptions;
use Illuminate\Support\Collection;

/**
 * Turns a `Product` into the shape the admin screens expect.
 *
 * `id` is the SKU, not the primary key: the admin routes are `/admin/produk/
 * PRD-001`, which is readable and stable across environments. `stock` is the
 * sum across every branch — a product has no stock of its own.
 */
class ProductPresenter
{
    /**
     * @param  iterable<Product>  $products
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $products): array
    {
        return collect($products)->map(fn (Product $product) => self::toArray($product))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Product $product): array
    {
        $stock = $product->total_stock;

        return [
            'id' => $product->sku,
            'name' => $product->name,
            'category' => $product->category?->name,
            'seller' => $product->supplier?->name,
            'image' => $product->image_path,
            'price' => $product->price,
            'oldPrice' => $product->old_price,
            'stock' => $stock,
            'stockStatus' => AdminOptions::stockLabel($stock),
            'sold' => $product->sold_count,
            'rating' => number_format((float) $product->rating, 1),
            'status' => AdminOptions::toLabel(AdminOptions::PRODUCT_STATUSES, $product->status),
            'unit' => $product->unit,
            'prescription' => $product->requires_prescription,
            'blurb' => $product->blurb,
        ];
    }

    /**
     * The same record plus its per-branch stock, for the detail screen.
     *
     * Read-only here: adjusting a branch's stock belongs on the branch screens
     * (Fase 2.4), because a single number on the product form cannot say which
     * of ten branches it applies to.
     *
     * @return array<string, mixed>
     */
    public static function withBranches(Product $product): array
    {
        $branches = $product->stocks
            ->sortBy(fn ($stock) => $stock->branch?->name)
            ->map(fn ($stock) => [
                'id' => $stock->branch?->code,
                'name' => $stock->branch?->name,
                'kota' => $stock->branch?->kota,
                'quantity' => $stock->quantity,
                'reserved' => $stock->reserved_quantity,
                'available' => $stock->available,
                'isLow' => $stock->is_low,
            ])
            ->values()
            ->all();

        return [...self::toArray($product), 'branches' => $branches];
    }

    /**
     * Slimmed-down list for the order form's product picker.
     *
     * @param  iterable<Product>  $products
     * @return list<array<string, mixed>>
     */
    public static function options(iterable $products): array
    {
        return collect($products)
            ->map(fn (Product $product) => [
                'id' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return list<string>
     */
    public static function names(Collection $products): array
    {
        return $products->pluck('name')->all();
    }
}

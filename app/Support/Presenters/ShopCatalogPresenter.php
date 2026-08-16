<?php

namespace App\Support\Presenters;

use App\Models\Category;
use App\Models\Product;
use App\Support\AdminOptions;

/**
 * The catalogue as the storefront needs it.
 *
 * Shared with every non-admin page rather than passed to individual screens,
 * because the search overlay lives in the layout and can be opened from
 * anywhere — a per-page prop would leave it empty on most screens.
 *
 * Only active products appear. `status` here means availability, which is what
 * a shopper cares about, not the catalogue status an administrator sets.
 */
class ShopCatalogPresenter
{
    /**
     * @return array{products: list<array<string, mixed>>, categories: list<array<string, mixed>>}
     */
    public static function forStorefront(): array
    {
        $products = Product::query()
            ->active()
            ->with(['category', 'images'])
            ->withSum('stocks', 'quantity')
            ->orderBy('id')
            ->get();

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return [
            'products' => $products->map(fn (Product $product) => self::product($product))->all(),
            'categories' => $categories->map(fn (Category $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image_path ?? '/media/images/small/img-1.jpg',
                'status' => AdminOptions::toLabel(AdminOptions::CATEGORY_STATUSES, $category->status),
                'products' => (int) $category->products_count,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function product(Product $product): array
    {
        $stock = $product->total_stock;

        return [
            'id' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'category' => $product->category?->name,
            'image' => $product->image_path,
            'price' => $product->price,
            'oldPrice' => $product->old_price,
            'stock' => $stock,
            'sold' => $product->sold_count,
            'rating' => number_format((float) $product->rating, 1),
            'status' => AdminOptions::stockLabel($stock),
            'unit' => $product->unit,
            // Pack sizes are not modelled yet — one row per pack arrives with the
            // real product data in Fase 4.2. Until then the product page offers
            // the single unit the product is actually sold in.
            'variants' => [$product->unit],
            'prescription' => $product->requires_prescription,
            'blurb' => $product->blurb,
        ];
    }
}

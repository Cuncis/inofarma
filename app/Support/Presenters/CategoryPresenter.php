<?php

namespace App\Support\Presenters;

use App\Models\Category;
use App\Support\AdminOptions;

/**
 * Turns a `Category` into the shape the admin screens expect.
 *
 * `id` is the slug — the admin routes are `/admin/kategori/obat-bebas`.
 */
class CategoryPresenter
{
    /**
     * @param  iterable<Category>  $categories
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $categories): array
    {
        return collect($categories)->map(fn (Category $category) => self::toArray($category))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Category $category): array
    {
        return [
            'id' => $category->slug,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image_path ?? '/media/images/small/img-1.jpg',
            'status' => AdminOptions::toLabel(AdminOptions::CATEGORY_STATUSES, $category->status),
            'description' => $category->description,
            // `withCount('products')` fills this; without it, fall back to a count.
            'products' => (int) ($category->products_count ?? $category->products()->count()),
        ];
    }
}

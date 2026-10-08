<?php

namespace App\Support;

use App\Models\Category;

/**
 * Finds a category by name, creating (or restoring) it when an import
 * mentions one the shop has not seen yet.
 */
class CategoryResolver
{
    /** @var array<string, int> */
    private array $cache = [];

    public function id(string $name): int
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $category = Category::withTrashed()->where('name', $name)->first();

        if ($category && $category->trashed()) {
            $category->restore();
        }

        $category ??= Category::create([
            'name' => $name,
            'slug' => Slug::unique(Category::withTrashed(), $name),
            'status' => 'aktif',
            'position' => (int) Category::max('position') + 1,
            'image_path' => '/media/images/small/img-'.(Category::count() % 12 + 1).'.jpg',
        ]);

        $curatedIcon = $this->curatedIcon($category->slug);

        if ($curatedIcon !== null && $category->image_path !== $curatedIcon
            && (! $category->image_path || str_starts_with($category->image_path, '/media/images/small/'))) {
            $category->update(['image_path' => $curatedIcon]);
        }

        return $this->cache[$name] = $category->id;
    }

    /** The storefront's artwork for a category lives at `public/media/images/categories/{slug}.png`. */
    private function curatedIcon(string $slug): ?string
    {
        $path = "/media/images/categories/{$slug}.png";

        return file_exists(public_path($path)) ? $path : null;
    }
}

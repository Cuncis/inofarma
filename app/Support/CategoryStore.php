<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Session-backed category store.
 *
 * Same repository shape as `ProductStore`, with one extra concern: products
 * reference their category by name, so this store has to keep those references
 * honest — a rename cascades onto the products, and a delete is refused while
 * any product still sits in the category.
 */
class CategoryStore
{
    private const KEY = 'admin_categories';

    public function __construct(private readonly ProductStore $products) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::categories());
        }

        return array_values(session()->get(self::KEY));
    }

    /**
     * Categories with a live count of the products in each.
     *
     * @return list<array<string, mixed>>
     */
    public function withCounts(): array
    {
        $products = $this->products->all();

        return array_map(function (array $category) use ($products) {
            $category['products'] = count(array_filter(
                $products,
                fn (array $product) => $product['category'] === $category['name'],
            ));

            return $category;
        }, $this->all());
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_column($this->all(), 'name');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return Arr::first($this->all(), fn (array $category) => $category['id'] === $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function findOrFail(string $id): array
    {
        return $this->find($id) ?? abort(404, 'Kategori tidak ditemukan.');
    }

    /**
     * How many products sit in this category.
     */
    public function productCount(string $name): int
    {
        return count(array_filter(
            $this->products->all(),
            fn (array $product) => $product['category'] === $name,
        ));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $categories = $this->all();
        $slug = $this->uniqueSlug($attributes['slug'] ?? Str::slug($attributes['name']));

        $category = array_merge([
            'id' => $slug,
            'image' => '/media/images/small/img-'.(count($categories) % 6 + 1).'.jpg',
        ], $attributes, ['slug' => $slug]);

        $categories[] = $category;
        session()->put(self::KEY, $categories);

        return $category;
    }

    /**
     * Update a category, carrying a rename through to its products.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $categories = $this->all();

        foreach ($categories as $index => $category) {
            if ($category['id'] !== $id) {
                continue;
            }

            $updated = array_merge($category, $attributes);
            $categories[$index] = $updated;
            session()->put(self::KEY, $categories);

            if ($category['name'] !== $updated['name']) {
                $this->renameOnProducts($category['name'], $updated['name']);
            }

            return $updated;
        }

        abort(404, 'Kategori tidak ditemukan.');
    }

    /**
     * Remove a category. Refused while products still reference it.
     */
    public function delete(string $id): bool
    {
        $category = $this->findOrFail($id);

        if ($this->productCount($category['name']) > 0) {
            return false;
        }

        $remaining = array_values(array_filter(
            $this->all(),
            fn (array $existing) => $existing['id'] !== $id,
        ));

        session()->put(self::KEY, $remaining);

        return true;
    }

    public function reset(): void
    {
        session()->put(self::KEY, Catalog::categories());
    }

    /**
     * Point every product at the category's new name.
     */
    private function renameOnProducts(string $from, string $to): void
    {
        foreach ($this->products->all() as $product) {
            if ($product['category'] === $from) {
                $this->products->update($product['id'], ['category' => $to]);
            }
        }
    }

    /**
     * Slugs are the identifier, so they cannot collide.
     */
    private function uniqueSlug(string $slug): string
    {
        $slug = Str::slug($slug);
        $taken = array_column($this->all(), 'slug');
        $candidate = $slug;
        $suffix = 2;

        while (in_array($candidate, $taken, true)) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}

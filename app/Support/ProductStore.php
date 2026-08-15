<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Session-backed product store.
 *
 * Stands in for a database while the prototype has none: the catalogue is
 * seeded into the session on first read, and every write lands there, so
 * changes survive navigation without persisting across browsers or users.
 *
 * The public surface is deliberately repository-shaped — swapping this for an
 * Eloquent model means reimplementing these six methods and nothing else.
 */
class ProductStore
{
    private const KEY = 'admin_products';

    /**
     * Every product, in insertion order.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::products());
        }

        return array_values(session()->get(self::KEY));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return Arr::first($this->all(), fn (array $product) => $product['id'] === $id);
    }

    /**
     * Find a product or abort with a 404.
     *
     * @return array<string, mixed>
     */
    public function findOrFail(string $id): array
    {
        return $this->find($id) ?? abort(404, 'Produk tidak ditemukan.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $products = $this->all();

        $product = array_merge([
            'id' => $this->nextId($products),
            'image' => '/media/images/product/p-'.(count($products) % 12 + 1).'.png',
            'sold' => 0,
            'rating' => '0.0',
            'oldPrice' => null,
        ], $attributes);

        $products[] = $product;
        session()->put(self::KEY, $products);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $products = $this->all();

        foreach ($products as $index => $product) {
            if ($product['id'] === $id) {
                $products[$index] = array_merge($product, $attributes);
                session()->put(self::KEY, $products);

                return $products[$index];
            }
        }

        abort(404, 'Produk tidak ditemukan.');
    }

    public function delete(string $id): bool
    {
        $products = $this->all();
        $remaining = array_values(array_filter($products, fn (array $product) => $product['id'] !== $id));

        session()->put(self::KEY, $remaining);

        return count($remaining) !== count($products);
    }

    /**
     * Discard every change and go back to the seed catalogue.
     */
    public function reset(): void
    {
        session()->put(self::KEY, Catalog::products());
    }

    /**
     * Next sequential id, continuing past whatever the seed ended on.
     *
     * @param  list<array<string, mixed>>  $products
     */
    private function nextId(array $products): string
    {
        $highest = collect($products)
            ->map(fn (array $product) => (int) Str::after($product['id'], 'PRD-'))
            ->max() ?? 0;

        return 'PRD-'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}

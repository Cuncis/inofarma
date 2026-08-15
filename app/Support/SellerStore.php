<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Session-backed seller store.
 *
 * Products name their seller, so this store carries the same two obligations as
 * `CategoryStore`: a rename cascades onto the products, and a seller who still
 * stocks something cannot be removed.
 *
 * Product count and revenue are derived from the catalogue rather than stored,
 * so they cannot drift from what the seller actually sells.
 */
class SellerStore
{
    private const KEY = 'admin_sellers';

    public function __construct(private readonly ProductStore $products) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::sellers());
        }

        return array_values(session()->get(self::KEY));
    }

    /**
     * Sellers with their live product count and revenue.
     *
     * @return list<array<string, mixed>>
     */
    public function withStats(): array
    {
        return array_map(fn (array $seller) => $this->decorate($seller), $this->all());
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
        return Arr::first($this->all(), fn (array $seller) => $seller['id'] === $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function findOrFail(string $id): array
    {
        return $this->decorate($this->find($id) ?? abort(404, 'Penjual tidak ditemukan.'));
    }

    /**
     * Products stocked by a seller.
     *
     * @return list<array<string, mixed>>
     */
    public function productsFor(string $name): array
    {
        return array_values(array_filter(
            $this->products->all(),
            fn (array $product) => ($product['seller'] ?? null) === $name,
        ));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $sellers = $this->all();
        $logos = ['nike', 'dyson', 'huawei', 'gopro', 'zara', 'rolex', 'thenorthface'];

        $seller = array_merge([
            'id' => $this->nextId($sellers),
            'logo' => '/media/images/seller/'.$logos[count($sellers) % count($logos)].'.svg',
            'rating' => '0.0',
            'joined' => now()->translatedFormat('d M Y'),
        ], $attributes);

        $sellers[] = $seller;
        session()->put(self::KEY, $sellers);

        return $seller;
    }

    /**
     * Update a seller, carrying a rename through to their products.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $sellers = $this->all();

        foreach ($sellers as $index => $seller) {
            if ($seller['id'] !== $id) {
                continue;
            }

            $updated = array_merge($seller, $attributes);
            $sellers[$index] = $updated;
            session()->put(self::KEY, $sellers);

            if ($seller['name'] !== $updated['name']) {
                $this->renameOnProducts($seller['name'], $updated['name']);
            }

            return $updated;
        }

        abort(404, 'Penjual tidak ditemukan.');
    }

    /**
     * Remove a seller. Refused while they still stock products.
     */
    public function delete(string $id): bool
    {
        $seller = $this->find($id) ?? abort(404, 'Penjual tidak ditemukan.');

        if (count($this->productsFor($seller['name'])) > 0) {
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
        session()->put(self::KEY, Catalog::sellers());
    }

    /**
     * Attach the derived figures to a record.
     *
     * @param  array<string, mixed>  $seller
     * @return array<string, mixed>
     */
    private function decorate(array $seller): array
    {
        $products = $this->productsFor($seller['name']);

        $seller['products'] = count($products);
        $seller['revenue'] = array_sum(array_map(
            fn (array $product) => $product['price'] * $product['sold'],
            $products,
        ));

        return $seller;
    }

    private function renameOnProducts(string $from, string $to): void
    {
        foreach ($this->products->all() as $product) {
            if (($product['seller'] ?? null) === $from) {
                $this->products->update($product['id'], ['seller' => $to]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $sellers
     */
    private function nextId(array $sellers): string
    {
        $highest = collect($sellers)
            ->map(fn (array $seller) => (int) substr($seller['id'], 4))
            ->max() ?? 0;

        return 'SEL-'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}

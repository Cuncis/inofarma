<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Session-backed order store.
 *
 * Totals are derived from the line items on read, never stored — a stored total
 * would drift the moment a line changed. Line items keep their own copy of the
 * product name and unit price, so an order stays a faithful record of what was
 * charged even after the product is renamed, repriced or removed.
 *
 * Deliberately depends on nothing else: `CustomerStore` reads orders from here,
 * so a dependency in the other direction would be a cycle.
 */
class OrderStore
{
    private const KEY = 'admin_orders';

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::orders());
        }

        return array_map(
            fn (array $order) => $this->decorate($order),
            array_values(session()->get(self::KEY)),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return Arr::first($this->all(), fn (array $order) => $order['id'] === $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function findOrFail(string $id): array
    {
        return $this->find($id) ?? abort(404, 'Pesanan tidak ditemukan.');
    }

    /**
     * Orders placed by an email address.
     *
     * @return list<array<string, mixed>>
     */
    public function forCustomer(string $email): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $order) => $order['customerEmail'] === $email,
        ));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $orders = $this->raw();

        $order = array_merge([
            'id' => $this->nextId($orders),
            'date' => now()->translatedFormat('d M Y'),
            'note' => '',
        ], $attributes);

        $orders[] = $order;
        session()->put(self::KEY, $orders);

        return $this->decorate($order);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $orders = $this->raw();

        foreach ($orders as $index => $order) {
            if ($order['id'] === $id) {
                $orders[$index] = array_merge($order, $attributes);
                session()->put(self::KEY, $orders);

                return $this->decorate($orders[$index]);
            }
        }

        abort(404, 'Pesanan tidak ditemukan.');
    }

    /**
     * Remove an order. A completed one is a financial record — cancel it instead.
     */
    public function delete(string $id): bool
    {
        $order = $this->findOrFail($id);

        if ($order['status'] === 'Selesai') {
            return false;
        }

        $remaining = array_values(array_filter(
            $this->raw(),
            fn (array $existing) => $existing['id'] !== $id,
        ));

        session()->put(self::KEY, $remaining);

        return true;
    }

    public function reset(): void
    {
        session()->put(self::KEY, Catalog::orders());
    }

    /**
     * Stored records, without the derived figures.
     *
     * @return list<array<string, mixed>>
     */
    private function raw(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::orders());
        }

        return array_values(session()->get(self::KEY));
    }

    /**
     * Attach the derived money figures.
     *
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private function decorate(array $order): array
    {
        $order['subtotal'] = array_sum(array_map(
            fn (array $item) => $item['qty'] * $item['price'],
            $order['items'],
        ));
        $order['total'] = $order['subtotal'] + ($order['shipping'] ?? 0);
        $order['itemCount'] = array_sum(array_column($order['items'], 'qty'));

        return $order;
    }

    /**
     * @param  list<array<string, mixed>>  $orders
     */
    private function nextId(array $orders): string
    {
        $highest = collect($orders)
            ->map(fn (array $order) => (int) substr($order['id'], 4))
            ->max() ?? 2400;

        return 'INO-'.($highest + 1);
    }
}

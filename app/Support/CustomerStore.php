<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Session-backed customer store.
 *
 * Order history is keyed to a customer by email, so `orders` and `spent` are
 * derived on read rather than stored on the record — a stored counter would
 * drift the moment an order changed.
 *
 * A customer with orders can be deactivated but not deleted; removing them
 * would leave that history pointing at nobody.
 */
class CustomerStore
{
    private const KEY = 'admin_customers';

    public function __construct(private readonly OrderStore $orders) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        if (! session()->has(self::KEY)) {
            session()->put(self::KEY, Catalog::customers());
        }

        return array_values(session()->get(self::KEY));
    }

    /**
     * Every customer with their order count and lifetime spend.
     *
     * @return list<array<string, mixed>>
     */
    public function withStats(): array
    {
        return array_map(function (array $customer) {
            $orders = $this->ordersFor($customer['email']);

            $customer['orders'] = count($orders);
            $customer['spent'] = array_sum(array_column($orders, 'total'));

            return $customer;
        }, $this->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return Arr::first($this->all(), fn (array $customer) => $customer['id'] === $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function findOrFail(string $id): array
    {
        $customer = $this->find($id) ?? abort(404, 'Pelanggan tidak ditemukan.');
        $orders = $this->ordersFor($customer['email']);

        $customer['orders'] = count($orders);
        $customer['spent'] = array_sum(array_column($orders, 'total'));

        return $customer;
    }

    /**
     * Orders belonging to an email address, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function ordersFor(string $email): array
    {
        return $this->orders->forCustomer($email);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $customers = $this->all();

        $customer = array_merge([
            'id' => $this->nextId($customers),
            'avatar' => '/media/images/users/avatar-'.(count($customers) % 12 + 1).'.jpg',
            'joined' => now()->translatedFormat('d M Y'),
        ], $attributes);

        $customers[] = $customer;
        session()->put(self::KEY, $customers);

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $customers = $this->all();

        foreach ($customers as $index => $customer) {
            if ($customer['id'] === $id) {
                $customers[$index] = array_merge($customer, $attributes);
                session()->put(self::KEY, $customers);

                return $customers[$index];
            }
        }

        abort(404, 'Pelanggan tidak ditemukan.');
    }

    /**
     * Remove a customer. Refused while they still have order history.
     */
    public function delete(string $id): bool
    {
        $customer = $this->findOrFail($id);

        if (count($this->ordersFor($customer['email'])) > 0) {
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
        session()->put(self::KEY, Catalog::customers());
    }

    /**
     * @param  list<array<string, mixed>>  $customers
     */
    private function nextId(array $customers): string
    {
        $highest = collect($customers)
            ->map(fn (array $customer) => (int) substr($customer['id'], 4))
            ->max() ?? 0;

        return 'CUS-'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}

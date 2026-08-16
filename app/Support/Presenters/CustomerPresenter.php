<?php

namespace App\Support\Presenters;

use App\Models\Customer;
use App\Support\AdminOptions;

/**
 * Turns a `Customer` into the shape the admin screens expect.
 *
 * `city` and `address` come from the customer's default address, not from the
 * customer row — a customer can have several addresses, and the shop needs all
 * of them at checkout.
 */
class CustomerPresenter
{
    /**
     * @param  iterable<Customer>  $customers
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $customers): array
    {
        return collect($customers)->map(fn (Customer $customer) => self::toArray($customer))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Customer $customer): array
    {
        $address = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first();

        return [
            'id' => $customer->code,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'city' => $address?->kota,
            'address' => $address?->address_line,
            'avatar' => $customer->avatar_path ?? '/media/images/users/avatar-1.jpg',
            'status' => AdminOptions::toLabel(AdminOptions::CUSTOMER_STATUSES, $customer->status),
            'joined' => $customer->created_at?->translatedFormat('d M Y'),
            'orders' => (int) ($customer->orders_count ?? $customer->orders()->count()),
            // `spent_total` is the constrained withSum the controllers add; the
            // accessor is the same rule expressed one record at a time.
            'spent' => (int) ($customer->spent_total ?? $customer->lifetime_spend),
        ];
    }

    /**
     * Slimmed-down list for the order form's customer picker.
     *
     * @param  iterable<Customer>  $customers
     * @return list<array<string, string>>
     */
    public static function options(iterable $customers): array
    {
        return collect($customers)
            ->map(fn (Customer $customer) => ['email' => $customer->email, 'name' => $customer->name])
            ->values()
            ->all();
    }
}

<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Money columns default to zero — recalculate them from the line items after
     * attaching those, the same way the controller does.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'INO-'.fake()->unique()->numberBetween(2000, 9999),
            'branch_id' => Branch::factory(),
            'customer_id' => Customer::factory(),
            'fulfilment' => 'antar',
            'status' => 'diproses',
            'payment_method' => 'Transfer Bank',
            'payment_status' => 'belum bayar',
            'subtotal' => 0,
            'shipping_total' => 20000,
            'grand_total' => 20000,
            'note' => '',
        ];
    }

    public function selesai(): static
    {
        return $this->state([
            'status' => 'selesai',
            'payment_status' => 'lunas',
            'paid_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function pickup(): static
    {
        return $this->state(['fulfilment' => 'ambil', 'shipping_total' => 0]);
    }
}

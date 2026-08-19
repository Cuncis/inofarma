<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'courier_company' => 'jne',
            'courier_type' => 'reg',
            'courier_name' => 'JNE',
            'courier_service_name' => 'REG',
            'price' => fake()->numberBetween(9000, 35000),
        ];
    }

    public function booked(): static
    {
        return $this->state(fn () => [
            'biteship_order_id' => 'biteship-'.fake()->uuid(),
            'tracking_id' => 'BST-'.fake()->numerify('########'),
            'waybill_id' => strtoupper(fake()->bothify('??########')),
            'courier_link' => 'https://biteship.com/tracking/BST-'.fake()->numerify('########'),
            'status' => 'confirmed',
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->booked()->state([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}

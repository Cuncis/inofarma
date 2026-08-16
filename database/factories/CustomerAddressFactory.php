<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => 'Rumah',
            'recipient_name' => fake()->name(),
            'phone' => '+62 8'.fake()->numerify('##-####-####'),
            'address_line' => fake()->streetAddress(),
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'postal_code' => fake()->numerify('#####'),
            'latitude' => fake()->randomFloat(7, -6.40, -6.10),
            'longitude' => fake()->randomFloat(7, 106.65, 106.95),
            'is_default' => false,
        ];
    }

    public function isDefault(): static
    {
        return $this->state(['is_default' => true]);
    }
}

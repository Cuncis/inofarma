<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'SEL-'.fake()->unique()->numerify('###'),
            'name' => 'Apotek '.fake()->unique()->lastName(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+62 21 '.fake()->numerify('#### ####'),
            'license_number' => 'SIA/2025/'.fake()->unique()->numerify('#####'),
            'address_line' => fake()->streetAddress(),
            'kota' => fake()->city(),
            'provinsi' => 'DKI Jakarta',
            'payment_term_days' => 30,
            'status' => 'aktif',
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }
}

<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('KUPON##??')),
            'type' => 'persentase',
            'value' => fake()->numberBetween(5, 50),
            'minimum_purchase' => 50000,
            'quota' => 500,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(3),
            'status' => 'aktif',
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }

    public function habis(): static
    {
        return $this->state(fn (array $attributes) => ['used_count' => $attributes['quota'] ?? 500]);
    }
}

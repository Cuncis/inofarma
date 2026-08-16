<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryBatch>
 */
class InventoryBatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'batch_number' => 'B'.fake()->unique()->numerify('#######'),
            'expires_at' => now()->addMonths(fake()->numberBetween(6, 24)),
            'quantity' => fake()->numberBetween(1, 200),
            'received_at' => now()->subDays(30),
        ];
    }

    /** Already past its expiry date — must never be picked by FEFO. */
    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDays(fake()->numberBetween(1, 90))]);
    }

    public function expiringWithin(int $days): static
    {
        return $this->state(['expires_at' => now()->addDays($days)]);
    }
}

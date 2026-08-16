<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
class StockTransferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'TRF-'.fake()->unique()->numerify('####'),
            'from_branch_id' => Branch::factory(),
            'to_branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 50),
            'status' => 'diminta',
            'note' => '',
        ];
    }

    public function dikirim(): static
    {
        return $this->state([
            'status' => 'dikirim',
            'shipped_at' => now(),
            'batches_shipped' => [],
        ]);
    }

    public function diterima(): static
    {
        return $this->state([
            'status' => 'diterima',
            'shipped_at' => now()->subHour(),
            'received_at' => now(),
            'batches_shipped' => [],
        ]);
    }
}

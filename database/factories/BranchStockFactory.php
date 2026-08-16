<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchStock>
 */
class BranchStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 200),
            'reserved_quantity' => 0,
            'reorder_point' => 20,
            'price_override' => null,
            'is_listed' => true,
        ];
    }

    public function empty(): static
    {
        return $this->state(['quantity' => 0]);
    }

    /** Below the reorder point, so the low-stock badge shows. */
    public function low(): static
    {
        return $this->state(['quantity' => 5, 'reorder_point' => 20]);
    }
}

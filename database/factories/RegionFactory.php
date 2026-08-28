<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * A bare level-1 (provinsi) row by default — tests build a chain
     * explicitly via overrides (`code`, `parent_code`, `level`) rather than
     * this factory inferring a hierarchy on its own.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => (string) fake()->unique()->numberBetween(10, 99),
            'parent_code' => null,
            'level' => 1,
            'name' => fake()->city(),
            'postal_code' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => 'pilihan',
            'values' => [fake()->word(), fake()->word(), fake()->word()],
        ];
    }

    public function teks(): static
    {
        return $this->state(['type' => 'teks', 'values' => null]);
    }
}

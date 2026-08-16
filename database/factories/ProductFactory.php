<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true)).' '.fake()->numberBetween(50, 900).'mg';

        return [
            'sku' => 'PRD-'.fake()->unique()->numerify('###'),
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'price' => fake()->numberBetween(5, 200) * 500,
            'old_price' => null,
            'unit' => fake()->randomElement(['Strip', 'Botol', 'Box', 'Tablet', 'Pcs']),
            'blurb' => fake()->sentence(14),
            'drug_class' => 'bebas',
            'requires_prescription' => false,
            'weight_grams' => 150,
            'sold_count' => fake()->numberBetween(0, 2000),
            'rating' => fake()->randomFloat(1, 3.5, 5),
            'status' => 'aktif',
        ];
    }

    /** Obat keras: only dispensable against a prescription. */
    public function keras(): static
    {
        return $this->state([
            'drug_class' => 'keras',
            'requires_prescription' => true,
        ]);
    }
}

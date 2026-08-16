<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(5, 200) * 500;
        $quantity = fake()->numberBetween(1, 10);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'sku' => 'PRD-'.fake()->numerify('###'),
            'unit_price' => $price,
            'quantity' => $quantity,
            'line_total' => $price * $quantity,
        ];
    }

    /**
     * Snapshot a real product, the way the order flow does.
     */
    public function forProduct(Product $product, int $quantity = 1): static
    {
        return $this->state([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ]);
    }
}

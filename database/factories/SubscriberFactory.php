<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'status' => Subscriber::SUBSCRIBED,
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn () => [
            'status' => Subscriber::UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);
    }
}

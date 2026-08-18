<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => 'doku',
            'invoice_number' => 'INV-'.Str::upper(Str::random(10)),
            'status' => 'pending',
            'amount' => fake()->numberBetween(20000, 500000),
            'request_id' => (string) Str::uuid(),
            'expires_at' => now()->addHours(24),
        ];
    }

    public function success(): static
    {
        return $this->state(['status' => 'success', 'paid_at' => now()]);
    }

    public function expired(): static
    {
        return $this->state(['status' => 'expired', 'expires_at' => now()->subHour()]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CUS-'.fake()->unique()->numerify('###'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => '+62 8'.fake()->numerify('##-####-####'),
            'password' => Hash::make('password'),
            'avatar_path' => '/media/images/users/avatar-'.fake()->numberBetween(1, 12).'.jpg',
            'status' => 'aktif',
            'consent_at' => now(),
            'consent_version' => '1.0',
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }
}

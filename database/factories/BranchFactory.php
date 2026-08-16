<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Inofarma '.fake()->unique()->streetName();

        return [
            'code' => 'CB-'.fake()->unique()->numerify('###'),
            'name' => $name,
            'slug' => Str::slug($name),
            'address_line' => fake()->streetAddress(),
            'kota' => fake()->randomElement(['Jakarta Barat', 'Jakarta Selatan', 'Bogor', 'Tangerang Selatan']),
            'provinsi' => 'DKI Jakarta',
            // Jabodetabek bounding box, so distance sorting produces sane numbers.
            'latitude' => fake()->randomFloat(7, -6.40, -6.10),
            'longitude' => fake()->randomFloat(7, 106.65, 106.95),
            'phone' => '+62 21 '.fake()->numerify('#### ####'),
            'supports_delivery' => true,
            'supports_pickup' => true,
            'delivery_radius_km' => 10,
            'status' => 'aktif',
        ];
    }

    /** A branch with no coordinates, which every distance query must skip. */
    public function withoutCoordinates(): static
    {
        return $this->state(['latitude' => null, 'longitude' => null]);
    }

    public function tutupSementara(): static
    {
        return $this->state(['status' => 'tutup sementara']);
    }
}

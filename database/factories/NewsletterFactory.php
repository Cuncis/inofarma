<?php

namespace Database\Factories;

use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsletter>
 */
class NewsletterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Promo '.fake()->monthName(),
            'subject' => fake()->sentence(5),
            'preview_text' => fake()->sentence(8),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'status' => Newsletter::DRAFT,
        ];
    }
}

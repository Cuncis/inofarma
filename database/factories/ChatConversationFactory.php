<?php

namespace Database\Factories;

use App\Models\ChatConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatConversation>
 */
class ChatConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => ChatConversation::newToken(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => ChatConversation::OPEN,
            'admin_unread_count' => 0,
            'last_message_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(['status' => ChatConversation::CLOSED]);
    }

    public function needingReply(int $count = 1): static
    {
        return $this->state(['admin_unread_count' => $count]);
    }
}

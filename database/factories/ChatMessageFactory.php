<?php

namespace Database\Factories;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_conversation_id' => ChatConversation::factory(),
            'sender' => ChatMessage::FROM_VISITOR,
            'user_id' => null,
            'body' => fake()->sentence(),
            'visitor_seen_at' => null,
            'emailed_at' => null,
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(['sender' => ChatMessage::FROM_ADMIN]);
    }
}

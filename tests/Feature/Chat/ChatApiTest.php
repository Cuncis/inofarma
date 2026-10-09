<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /**
     * @return array<string, string>
     */
    private function startPayload(array $overrides = []): array
    {
        return [...['name' => 'Budi Santoso', 'email' => 'Budi@Example.com', 'message' => 'Apakah paracetamol ada?'], ...$overrides];
    }

    public function test_starting_a_chat_stores_the_conversation_and_first_message_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/chat', $this->startPayload())->assertCreated();

        $token = $response->json('token');
        $this->assertSame(40, strlen($token));
        $this->assertSame('Apakah paracetamol ada?', $response->json('messages.0.body'));
        $this->assertSame('pengunjung', $response->json('messages.0.sender'));

        $conversation = ChatConversation::where('token', $token)->firstOrFail();
        $this->assertSame('Budi Santoso', $conversation->name);
        $this->assertSame('budi@example.com', $conversation->email);
        $this->assertSame('terbuka', $conversation->status);
        $this->assertSame(1, $conversation->admin_unread_count);
        $this->assertCount(1, $conversation->messages);
    }

    public function test_the_newsletter_box_is_optional_and_only_subscribes_when_ticked(): void
    {
        $this->postJson('/api/chat', $this->startPayload(['email' => 'tidak@example.com']))->assertCreated();
        $this->assertDatabaseMissing('subscribers', ['email' => 'tidak@example.com']);

        $this->postJson('/api/chat', $this->startPayload(['email' => 'ya@example.com', 'subscribe' => true]))->assertCreated();
        $this->assertDatabaseHas('subscribers', ['email' => 'ya@example.com', 'status' => 'berlangganan']);
    }

    public function test_ticking_the_box_resubscribes_someone_who_had_unsubscribed_and_never_duplicates(): void
    {
        $subscriber = Subscriber::create(['email' => 'kembali@example.com', 'status' => Subscriber::SUBSCRIBED]);
        $subscriber->unsubscribe();
        $this->assertFalse($subscriber->fresh()->isSubscribed());

        $this->postJson('/api/chat', $this->startPayload(['email' => 'kembali@example.com', 'subscribe' => true]))->assertCreated();
        $this->postJson('/api/chat', $this->startPayload(['email' => 'kembali@example.com', 'subscribe' => true]))->assertCreated();

        $this->assertTrue($subscriber->fresh()->isSubscribed());
        $this->assertSame(1, Subscriber::where('email', 'kembali@example.com')->count());
    }

    public function test_name_email_and_message_are_all_required_and_the_email_must_be_valid(): void
    {
        $this->postJson('/api/chat', $this->startPayload(['name' => '']))->assertJsonValidationErrors('name');
        $this->postJson('/api/chat', $this->startPayload(['email' => 'bukan-email']))->assertJsonValidationErrors('email');
        $this->postJson('/api/chat', $this->startPayload(['message' => '']))->assertJsonValidationErrors('message');
        $this->postJson('/api/chat', $this->startPayload(['message' => str_repeat('a', 1001)]))->assertJsonValidationErrors('message');

        $this->assertDatabaseCount('chat_conversations', 0);
    }

    public function test_a_filled_honeypot_is_ignored_without_telling_the_bot(): void
    {
        $this->postJson('/api/chat', $this->startPayload(['website' => 'http://spam.test']))->assertStatus(422);

        $this->assertDatabaseCount('chat_conversations', 0);
    }

    public function test_the_visitor_can_send_more_messages_with_their_token(): void
    {
        $token = $this->postJson('/api/chat', $this->startPayload())->json('token');

        $this->postJson('/api/chat/pesan', ['message' => 'Terima kasih'], ['X-Chat-Token' => $token])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Terima kasih');

        $conversation = ChatConversation::where('token', $token)->first();
        $this->assertCount(2, $conversation->messages);
        $this->assertSame(2, $conversation->admin_unread_count);
    }

    public function test_a_missing_wrong_or_malformed_token_is_a_404(): void
    {
        $this->postJson('/api/chat/pesan', ['message' => 'Halo'])->assertNotFound();
        $this->postJson('/api/chat/pesan', ['message' => 'Halo'], ['X-Chat-Token' => str_repeat('x', 40)])->assertNotFound();
        $this->getJson('/api/chat', ['X-Chat-Token' => 'pendek'])->assertNotFound();
        $this->getJson('/api/chat?token='.ChatConversation::factory()->create()->token)->assertNotFound();
    }

    public function test_polling_returns_only_messages_after_the_given_id_and_one_visitor_cannot_read_anothers_chat(): void
    {
        $mine = ChatConversation::factory()->create();
        $theirs = ChatConversation::factory()->create();
        $first = ChatMessage::factory()->for($mine, 'conversation')->create(['body' => 'pertama']);
        $second = ChatMessage::factory()->for($mine, 'conversation')->fromAdmin()->create(['body' => 'kedua']);
        ChatMessage::factory()->for($theirs, 'conversation')->create(['body' => 'rahasia orang lain']);

        $all = $this->getJson('/api/chat', ['X-Chat-Token' => $mine->token])->assertOk();
        $this->assertSame(['pertama', 'kedua'], collect($all->json('messages'))->pluck('body')->all());

        $newer = $this->getJson("/api/chat?after={$first->id}", ['X-Chat-Token' => $mine->token])->assertOk();
        $this->assertSame([$second->id], collect($newer->json('messages'))->pluck('id')->all());
    }

    public function test_admin_replies_count_as_seen_only_when_the_window_is_open(): void
    {
        $conversation = ChatConversation::factory()->create();
        $reply = ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create();

        $this->getJson('/api/chat?open=0', ['X-Chat-Token' => $conversation->token])
            ->assertOk()
            ->assertJsonPath('unread', 1);
        $this->assertNull($reply->fresh()->visitor_seen_at);

        $this->getJson('/api/chat?open=1', ['X-Chat-Token' => $conversation->token])
            ->assertOk()
            ->assertJsonPath('unread', 0);
        $this->assertNotNull($reply->fresh()->visitor_seen_at);
    }

    public function test_writing_to_a_closed_chat_reopens_it(): void
    {
        $conversation = ChatConversation::factory()->closed()->create();

        $this->postJson('/api/chat/pesan', ['message' => 'Masih ada pertanyaan'], ['X-Chat-Token' => $conversation->token])->assertCreated();

        $this->assertSame('terbuka', $conversation->fresh()->status);
    }

    public function test_starting_chats_is_rate_limited_per_visitor(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/chat', $this->startPayload(['email' => "u{$i}@example.com"]))->assertCreated();
        }

        $this->postJson('/api/chat', $this->startPayload(['email' => 'u6@example.com']))->assertStatus(429);
    }
}

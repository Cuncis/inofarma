<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\Admin\NewChatMessage;
use App\Notifications\ChatReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ChatNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function staffWith(string ...$permissions): User
    {
        $user = User::factory()->create(['is_active' => true]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    public function test_staff_with_inbox_access_are_notified_when_a_wait_begins_but_not_for_every_line(): void
    {
        Notification::fake();
        $viewer = $this->staffWith('Inbox:Lihat');
        $other = $this->staffWith('Produk:Lihat');
        $inactive = $this->staffWith('Inbox:Lihat');
        $inactive->update(['is_active' => false]);

        $conversation = ChatConversation::factory()->create(['admin_unread_count' => 0]);

        ChatMessage::factory()->for($conversation, 'conversation')->create();
        ChatMessage::factory()->for($conversation, 'conversation')->create();
        ChatMessage::factory()->for($conversation, 'conversation')->create();

        Notification::assertSentToTimes($viewer, NewChatMessage::class, 1);
        Notification::assertNotSentTo($other, NewChatMessage::class);
        Notification::assertNotSentTo($inactive, NewChatMessage::class);
        $this->assertSame(3, $conversation->fresh()->admin_unread_count);
    }

    public function test_a_new_wait_after_an_admin_reply_notifies_again(): void
    {
        Notification::fake();
        $viewer = $this->staffWith('Inbox:Lihat');
        $conversation = ChatConversation::factory()->create(['admin_unread_count' => 0]);

        ChatMessage::factory()->for($conversation, 'conversation')->create();
        ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create();
        ChatMessage::factory()->for($conversation, 'conversation')->create();

        Notification::assertSentToTimes($viewer, NewChatMessage::class, 2);
    }

    public function test_an_admin_reply_clears_the_unread_badge_and_queues_an_email_to_the_visitor(): void
    {
        Notification::fake();
        $conversation = ChatConversation::factory()->create(['email' => 'budi@example.com', 'admin_unread_count' => 3]);

        $reply = ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create(['body' => 'Ada, silakan ke Kalisari.']);

        $this->assertSame(0, $conversation->fresh()->admin_unread_count);

        Notification::assertSentOnDemand(ChatReplied::class, function (ChatReplied $notification, array $channels, object $notifiable) use ($reply) {
            return $notifiable->routes['mail'] === ['budi@example.com' => $reply->conversation->name]
                && $channels === ['mail']
                && $notification->shouldSend($notifiable, 'mail') === true;
        });
    }

    public function test_the_email_is_skipped_when_the_visitor_already_saw_the_reply_in_the_chat(): void
    {
        Notification::fake();
        $conversation = ChatConversation::factory()->create();
        $reply = ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create();
        $reply->update(['visitor_seen_at' => now()]);

        $this->assertFalse((new ChatReplied($reply))->shouldSend(new AnonymousNotifiable, 'mail'));
    }

    public function test_the_email_carries_every_unseen_reply_once_and_a_link_back_to_the_chat(): void
    {
        Notification::fake();
        $conversation = ChatConversation::factory()->create(['name' => 'Budi']);
        $first = ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create(['body' => 'Balasan satu']);
        $second = ChatMessage::factory()->for($conversation, 'conversation')->fromAdmin()->create(['body' => 'Balasan dua']);

        $mail = (new ChatReplied($second))->toMail(new AnonymousNotifiable);
        $rendered = implode("\n", $mail->introLines);

        $this->assertStringContainsString('Balasan satu', $rendered);
        $this->assertStringContainsString('Balasan dua', $rendered);
        $this->assertStringContainsString('?chat='.$conversation->token, $mail->actionUrl);
        $this->assertNotNull($first->fresh()->emailed_at);

        // The earlier reply was in that email already, so its own email is skipped.
        $this->assertFalse((new ChatReplied($first))->shouldSend(new AnonymousNotifiable, 'mail'));
    }

    public function test_the_email_is_delayed_by_the_configured_minutes(): void
    {
        Notification::fake();
        config(['chat.email_delay_minutes' => 7]);
        $this->travelTo(now()->startOfMinute());

        $reply = ChatMessage::factory()->fromAdmin()->create();

        $delay = (new ChatReplied($reply))->withDelay(new AnonymousNotifiable)['mail'];

        $this->assertTrue($delay->equalTo(now()->addMinutes(7)));
    }
}

<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\ChatConversations\ChatConversationResource;
use App\Filament\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Resources\ChatConversations\Pages\ViewChatConversation;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ChatReplied;
use App\Support\ChatSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class ChatInboxTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function signInAs(string $roleName, array $permissions): User
    {
        $this->post('/admin/keluar');

        Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true]);
        $user->assignRole($roleName);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);

        return $user;
    }

    private function conversationWithMessage(array $conversation = [], string $body = 'Apakah paracetamol ada?'): ChatConversation
    {
        Notification::fake();

        $conversation = ChatConversation::factory()->create($conversation);
        ChatMessage::factory()->for($conversation, 'conversation')->create(['body' => $body]);

        return $conversation->fresh();
    }

    public function test_the_list_shows_conversations_with_their_last_message_and_unread_count(): void
    {
        $waiting = $this->conversationWithMessage(['name' => 'Budi Santoso'], 'Apakah paracetamol ada?');
        $answered = $this->conversationWithMessage(['name' => 'Siti Aminah', 'admin_unread_count' => 0]);
        $answered->update(['admin_unread_count' => 0]);

        Livewire::test(ListChatConversations::class)
            ->assertCanSeeTableRecords([$waiting, $answered])
            ->assertSee('Budi Santoso')
            ->assertSee('Apakah paracetamol ada?')
            ->filterTable('needs_reply')
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$answered]);
    }

    public function test_staff_can_set_the_welcome_message_and_quick_questions(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Selamat datang di Inofarma!',
                'quick_questions' => [['question' => 'Apakah obat ini ready?', 'url' => '/shop'], ['question' => 'Jam buka apotek?', 'url' => null]],
            ])
            ->assertHasNoActionErrors();

        $this->assertSame('Selamat datang di Inofarma!', ChatSettings::welcomeMessage());
        $this->assertSame([
            ['question' => 'Apakah obat ini ready?', 'url' => '/shop', 'type' => 'message', 'answer' => null, 'phone' => null],
            ['question' => 'Jam buka apotek?', 'url' => null, 'type' => 'message', 'answer' => null, 'phone' => null],
        ], ChatSettings::quickQuestions());
    }

    public function test_a_shortcut_can_be_the_track_order_form_and_then_carries_no_link(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Halo',
                'quick_questions' => [['type' => 'track_order', 'question' => 'Lacak Pesanan', 'url' => '/shop']],
            ])
            ->assertHasNoActionErrors();

        $this->assertSame([['question' => 'Lacak Pesanan', 'url' => null, 'type' => 'track_order', 'answer' => null, 'phone' => null]], ChatSettings::quickQuestions());
    }

    public function test_the_other_shortcut_types_keep_only_the_detail_they_need(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Halo',
                'quick_questions' => [
                    ['type' => 'auto_reply', 'question' => 'Jam buka?', 'answer' => 'Buka 24 jam setiap hari.', 'url' => '/shop', 'phone' => '0812'],
                    ['type' => 'whatsapp', 'question' => 'Chat WhatsApp', 'phone' => '0812-1401-8964', 'answer' => 'abaikan'],
                    ['type' => 'phone', 'question' => 'Telepon kami', 'phone' => '+62 21 5095 9952'],
                    ['type' => 'ask_product', 'question' => 'Cek ketersediaan obat'],
                ],
            ])
            ->assertHasNoActionErrors();

        $this->assertSame([
            ['question' => 'Jam buka?', 'url' => null, 'type' => 'auto_reply', 'answer' => 'Buka 24 jam setiap hari.', 'phone' => null],
            ['question' => 'Chat WhatsApp', 'url' => null, 'type' => 'whatsapp', 'answer' => null, 'phone' => '6281214018964'],
            ['question' => 'Telepon kami', 'url' => null, 'type' => 'phone', 'answer' => null, 'phone' => '622150959952'],
            ['question' => 'Cek ketersediaan obat', 'url' => null, 'type' => 'ask_product', 'answer' => null, 'phone' => null],
        ], ChatSettings::quickQuestions());
    }

    public function test_an_auto_reply_needs_an_answer_and_a_call_needs_a_number(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Halo',
                'quick_questions' => [
                    ['type' => 'auto_reply', 'question' => 'Jam buka?', 'answer' => ''],
                    ['type' => 'phone', 'question' => 'Telepon kami', 'phone' => ''],
                ],
            ])
            ->assertHasActionErrors();
    }

    public function test_a_quick_question_link_must_be_a_path_or_an_http_url(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Halo',
                'quick_questions' => [['question' => 'Tanya', 'url' => 'javascript:alert(1)']],
            ])
            ->assertHasActionErrors();
    }

    public function test_the_welcome_message_is_required_and_questions_are_capped(): void
    {
        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', ['welcome_message' => '', 'quick_questions' => []])
            ->assertHasActionErrors(['welcome_message' => 'required']);

        Livewire::test(ListChatConversations::class)
            ->callAction('chatSettings', [
                'welcome_message' => 'Halo',
                'quick_questions' => collect(range(1, ChatSettings::MAX_QUESTIONS + 1))->map(fn ($n) => ['question' => "Tanya {$n}"])->all(),
            ])
            ->assertHasActionErrors();
    }

    public function test_the_storefront_gets_the_chat_settings_with_a_default_welcome(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('chatSettings.welcome', ChatSettings::DEFAULT_WELCOME)
            ->where('chatSettings.quickQuestions', [])
        );

        ChatSettings::save('Halo Sobat Ino', ['Ada promo?', '  ', 'Jam buka?']);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('chatSettings.welcome', 'Halo Sobat Ino')
            ->where('chatSettings.quickQuestions', [
                ['question' => 'Ada promo?', 'url' => null, 'type' => 'message', 'answer' => null, 'phone' => null],
                ['question' => 'Jam buka?', 'url' => null, 'type' => 'message', 'answer' => null, 'phone' => null],
            ])
        );
    }

    public function test_the_settings_action_is_hidden_without_the_reply_permission(): void
    {
        $this->signInAs('Pelihat Inbox', ['Inbox:Lihat']);

        Livewire::test(ListChatConversations::class)->assertActionHidden('chatSettings');
    }

    public function test_the_navigation_badge_counts_conversations_waiting_for_a_reply(): void
    {
        $this->assertNull(ChatConversationResource::getNavigationBadge());

        $this->conversationWithMessage();
        $this->conversationWithMessage();

        $this->assertSame('2', ChatConversationResource::getNavigationBadge());
    }

    public function test_opening_a_conversation_marks_it_read(): void
    {
        $conversation = $this->conversationWithMessage();
        $this->assertSame(1, $conversation->admin_unread_count);

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->assertSee('Apakah paracetamol ada?')
            ->assertSee($conversation->name);

        $this->assertSame(0, $conversation->fresh()->admin_unread_count);
    }

    public function test_a_new_visitor_message_shows_up_on_refresh_and_is_marked_read(): void
    {
        $conversation = $this->conversationWithMessage();

        $page = Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()]);

        ChatMessage::factory()->for($conversation, 'conversation')->create(['body' => 'Ada yang kedua']);
        $this->assertSame(1, $conversation->fresh()->admin_unread_count);

        $page->call('markRead')->assertSee('Ada yang kedua');

        $this->assertSame(0, $conversation->fresh()->admin_unread_count);
    }

    public function test_replying_stores_an_admin_message_under_the_staff_member_and_queues_the_email(): void
    {
        $conversation = $this->conversationWithMessage();
        Notification::fake();

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->set('reply', '  Ada, silakan ke Kalisari.  ')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('reply', '');

        $reply = $conversation->messages()->where('sender', 'admin')->firstOrFail();
        $this->assertSame('Ada, silakan ke Kalisari.', $reply->body);
        $this->assertSame(User::where('email', 'admin@inofarma.co.id')->value('id'), $reply->user_id);
        $this->assertNull($reply->visitor_seen_at);

        Notification::assertSentOnDemand(ChatReplied::class);
    }

    public function test_an_empty_or_oversized_reply_is_rejected(): void
    {
        $conversation = $this->conversationWithMessage();

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->set('reply', '   ')->call('send')->assertHasErrors('reply')
            ->set('reply', str_repeat('a', 2001))->call('send')->assertHasErrors('reply');

        $this->assertSame(0, $conversation->messages()->where('sender', 'admin')->count());
    }

    public function test_a_conversation_can_be_closed_and_reopened(): void
    {
        $conversation = $this->conversationWithMessage();

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->callAction('close')
            ->assertHasNoActionErrors();
        $this->assertSame('ditutup', $conversation->fresh()->status);

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->callAction('reopen');
        $this->assertSame('terbuka', $conversation->fresh()->status);
    }

    public function test_inbox_needs_inbox_lihat_and_replying_needs_inbox_balas(): void
    {
        $conversation = $this->conversationWithMessage();

        $this->signInAs('Tanpa Inbox', ['Produk:Lihat']);
        $this->get('/admin/inbox')->assertForbidden();
        $this->get("/admin/inbox/{$conversation->getKey()}")->assertForbidden();

        $this->signInAs('Hanya Lihat', ['Inbox:Lihat']);
        $this->get('/admin/inbox')->assertOk();
        $this->get("/admin/inbox/{$conversation->getKey()}")->assertOk()->assertDontSee(__('Kirim Balasan'));

        Livewire::test(ViewChatConversation::class, ['record' => $conversation->getKey()])
            ->set('reply', 'Coba balas')
            ->call('send')
            ->assertForbidden();
        $this->assertSame(0, $conversation->messages()->where('sender', 'admin')->count());

        $this->signInAs('Lihat dan Balas', ['Inbox:Lihat', 'Inbox:Balas']);
        $this->get("/admin/inbox/{$conversation->getKey()}")->assertOk()->assertSee(__('Kirim Balasan'));
    }

    public function test_the_seeded_roles_get_the_inbox_permissions_where_intended(): void
    {
        $this->assertTrue(Role::findByName('Super Admin', 'web')->hasPermissionTo('Inbox:Balas'));
        $this->assertTrue(Role::findByName('Manajer Area', 'web')->hasPermissionTo('Inbox:Lihat'));
        $this->assertTrue(Role::findByName('APJ Cabang', 'web')->hasPermissionTo('Inbox:Balas'));
        $this->assertFalse(Role::findByName('Kasir', 'web')->hasPermissionTo('Inbox:Lihat'));
    }
}

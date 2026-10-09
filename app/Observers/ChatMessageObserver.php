<?php

namespace App\Observers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\Admin\NewChatMessage;
use App\Notifications\ChatReplied;
use Illuminate\Support\Facades\Notification;

/**
 * Everything that follows a chat message being written, in one place, like the
 * order and stock observers.
 *
 * A visitor message bumps the inbox badge and pings the staff, but only when it
 * starts or restarts a wait (the first unread message), not on every line of a
 * long burst. An admin reply clears the badge, and queues the email that goes
 * out if the visitor never sees it in the chat window.
 */
class ChatMessageObserver
{
    public function created(ChatMessage $message): void
    {
        $conversation = $message->conversation;

        if ($message->isFromAdmin()) {
            $this->handleAdminReply($conversation, $message);

            return;
        }

        $this->handleVisitorMessage($conversation, $message);
    }

    private function handleVisitorMessage(ChatConversation $conversation, ChatMessage $message): void
    {
        $startsWait = $conversation->admin_unread_count === 0;

        $conversation->update([
            'status' => ChatConversation::OPEN,
            'admin_unread_count' => $conversation->admin_unread_count + 1,
            'last_message_at' => $message->created_at,
        ]);

        if (! $startsWait) {
            return;
        }

        $staff = User::permission('Inbox:Lihat')->where('is_active', true)->get();

        if ($staff->isNotEmpty()) {
            Notification::send($staff, new NewChatMessage($conversation, $message));
        }
    }

    private function handleAdminReply(ChatConversation $conversation, ChatMessage $message): void
    {
        $conversation->update([
            'status' => ChatConversation::OPEN,
            'admin_unread_count' => 0,
            'last_message_at' => $message->created_at,
        ]);

        Notification::route('mail', [$conversation->email => $conversation->name])
            ->notify(new ChatReplied($message));
    }
}

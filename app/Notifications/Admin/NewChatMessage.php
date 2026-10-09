<?php

namespace App\Notifications\Admin;

use App\Filament\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * A visitor wrote in the chat and nobody has answered yet. Database channel
 * only, shown in the admin panel's bell, like the other admin notifications;
 * fired by `App\Observers\ChatMessageObserver`.
 */
class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ChatConversation $conversation,
        private readonly ChatMessage $message,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title("Pesan baru dari {$this->conversation->name}")
            ->body(Str::limit($this->message->body, 120))
            ->actions([
                Action::make('view')
                    ->label('Buka Inbox')
                    ->url(ChatConversationResource::getUrl('view', ['record' => $this->conversation])),
            ])
            ->getDatabaseMessage();
    }
}

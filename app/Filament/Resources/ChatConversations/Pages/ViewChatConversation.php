<?php

namespace App\Filament\Resources\ChatConversations\Pages;

use App\Filament\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * One conversation: the thread, a reply box, and close / reopen. Opening it
 * counts as reading it, and the page refreshes itself every few seconds so a
 * visitor's new message appears without a reload.
 */
class ViewChatConversation extends ViewRecord
{
    protected static string $resource = ChatConversationResource::class;

    protected string $view = 'filament.resources.chat-conversations.pages.view-chat-conversation';

    public string $reply = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->markRead();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->email;
    }

    public function canReply(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Inbox:Balas');
    }

    /** Clears the "belum dibaca" badge once the admin has the thread open. */
    public function markRead(): void
    {
        $conversation = $this->getRecord();

        if ($conversation->admin_unread_count > 0) {
            $conversation->update(['admin_unread_count' => 0]);
            $this->dispatch('refresh-sidebar');
        }
    }

    public function send(): void
    {
        abort_unless($this->canReply(), 403);

        $this->validate(['reply' => ['required', 'string', 'max:2000']], attributes: ['reply' => __('Balasan')]);

        $this->getRecord()->messages()->create([
            'sender' => ChatMessage::FROM_ADMIN,
            'user_id' => Auth::guard('web')->id(),
            'body' => trim($this->reply),
        ]);

        $this->reply = '';

        Notification::make()->success()->title(__('Balasan terkirim'))->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close')
                ->label(__('Tutup Percakapan'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('gray')
                ->visible(fn () => $this->canReply() && $this->getRecord()->status === ChatConversation::OPEN)
                ->requiresConfirmation()
                ->action(fn () => $this->getRecord()->update(['status' => ChatConversation::CLOSED])),
            Action::make('reopen')
                ->label(__('Buka Kembali'))
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('gray')
                ->visible(fn () => $this->canReply() && $this->getRecord()->status === ChatConversation::CLOSED)
                ->action(fn () => $this->getRecord()->update(['status' => ChatConversation::OPEN])),
        ];
    }
}

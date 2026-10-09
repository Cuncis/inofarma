<?php

namespace App\Filament\Resources\ChatConversations;

use App\Filament\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Resources\ChatConversations\Pages\ViewChatConversation;
use App\Filament\Resources\ChatConversations\Tables\ChatConversationsTable;
use App\Models\ChatConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Inbox: every chat a storefront visitor started, newest activity first. Open
 * one to read the thread and reply; the visitor sees the reply in their chat
 * window, or by email if they have left. Records are never edited or deleted
 * here, only answered, closed and reopened.
 */
class ChatConversationResource extends Resource
{
    protected static ?string $model = ChatConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'inbox';

    protected static ?int $navigationSort = -6;

    public static function getNavigationLabel(): string
    {
        return __('Inbox');
    }

    public static function getModelLabel(): string
    {
        return __('Percakapan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Percakapan');
    }

    /** Conversations waiting for an answer. */
    public static function getNavigationBadge(): ?string
    {
        $waiting = ChatConversation::needsReply()->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Percakapan yang belum dibalas');
    }

    public static function table(Table $table): Table
    {
        return ChatConversationsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Inbox:Lihat');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatConversations::route('/'),
            'view' => ViewChatConversation::route('/{record}'),
        ];
    }
}

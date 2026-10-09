<?php

namespace App\Filament\Resources\ChatConversations\Tables;

use App\Filament\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('latestMessage'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nama'))
                    ->description(fn (ChatConversation $record) => $record->email)
                    ->searchable(['name', 'email'])
                    ->weight('medium'),
                TextColumn::make('latestMessage.body')
                    ->label(__('Pesan Terakhir'))
                    ->limit(70)
                    ->color('gray'),
                TextColumn::make('admin_unread_count')
                    ->label(__('Belum Dibaca'))
                    ->badge()
                    ->sortable()
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? (string) $state : '0'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state) => $state === ChatConversation::OPEN ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) => self::statusLabels()[$state] ?? $state),
                TextColumn::make('last_message_at')
                    ->label(__('Aktivitas Terakhir'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Filter::make('needs_reply')
                    ->label(__('Perlu dibalas'))
                    ->query(fn (Builder $query) => $query->needsReply()),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(self::statusLabels()),
            ])
            ->recordUrl(fn (ChatConversation $record) => ChatConversationResource::getUrl('view', ['record' => $record]))
            ->poll('10s')
            ->emptyStateHeading(__('Belum ada percakapan'))
            ->emptyStateDescription(__('Pesan dari pengunjung toko akan muncul di sini.'));
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            ChatConversation::OPEN => __('Terbuka'),
            ChatConversation::CLOSED => __('Ditutup'),
        ];
    }
}

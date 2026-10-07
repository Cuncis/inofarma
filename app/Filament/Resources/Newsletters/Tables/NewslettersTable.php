<?php

namespace App\Filament\Resources\Newsletters\Tables;

use App\Filament\Resources\Newsletters\NewsletterActions;
use App\Models\Newsletter;
use App\Support\AdminOptions;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewslettersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nama Kampanye'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(__('Subjek Email'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Newsletter::SENT => 'success',
                        Newsletter::SENDING => 'warning',
                        Newsletter::FAILED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::NEWSLETTER_STATUSES, $state)),
                TextColumn::make('sent_at')
                    ->label(__('Tanggal Kirim'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('recipient_count')
                    ->label(__('Penerima'))
                    ->alignRight(),
                TextColumn::make('success_count')
                    ->label(__('Berhasil'))
                    ->alignRight()
                    ->color('success'),
                TextColumn::make('failed_count')
                    ->label(__('Gagal'))
                    ->alignRight()
                    ->color(fn (int $state) => $state > 0 ? 'danger' : null),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                NewsletterActions::preview(fn (Newsletter $record) => $record),
                EditAction::make(),
            ])
            ->emptyStateHeading(__('Belum ada newsletter.'));
    }
}

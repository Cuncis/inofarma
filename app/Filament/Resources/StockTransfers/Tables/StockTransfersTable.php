<?php

namespace App\Filament\Resources\StockTransfers\Tables;

use App\Support\AdminOptions;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['fromBranch', 'toBranch', 'product', 'requestedBy']))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('fromBranch.name')
                    ->label('Dari'),
                TextColumn::make('toBranch.name')
                    ->label('Ke'),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->alignRight(),
                TextColumn::make('requestedBy.name')
                    ->label('Diminta Oleh')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::STOCK_TRANSFER_STATUSES, $state))
                    ->color(fn (string $state) => match ($state) {
                        'diterima' => 'success',
                        'dikirim' => 'info',
                        'dibatalkan' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::STOCK_TRANSFER_STATUSES)),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

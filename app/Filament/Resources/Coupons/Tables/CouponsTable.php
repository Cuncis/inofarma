<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->modifyQueryUsing(fn ($query) => $query->with('branches'))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::COUPON_TYPES, $state)),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(fn (Coupon $record) => $record->type === 'persentase'
                        ? "{$record->value}%"
                        : Money::rupiah($record->value)),
                TextColumn::make('minimum_purchase')
                    ->label('Min. Belanja')
                    ->alignRight()
                    ->formatStateUsing(fn (?int $state) => $state ? Money::rupiah($state) : '—'),
                TextColumn::make('used_count')
                    ->label('Terpakai')
                    ->alignRight(),
                TextColumn::make('branches.name')
                    ->label('Cabang')
                    ->badge()
                    ->limitList(2)
                    ->placeholder('Semua cabang'),
                TextColumn::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Coupon $record) => match (true) {
                        $record->is_exhausted => 'Habis',
                        $record->is_expired => 'Kedaluwarsa',
                        default => AdminOptions::toLabel(AdminOptions::COUPON_STATUSES, $record->status),
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

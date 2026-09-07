<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Models\Branch;
use App\Support\AdminOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Cabang')
                    ->searchable(),
                TextColumn::make('kota')
                    ->label('Kota')
                    ->searchable(),
                TextColumn::make('stocks_count')
                    ->label('Produk Distok')
                    ->counts('stocks')
                    ->alignRight(),
                IconColumn::make('is_open_now')
                    ->label('Buka Sekarang')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::BRANCH_STATUSES, $state)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // Same guard as the legacy admin: a branch with stock on the
                // shelf or orders in its history can't be deleted, only closed
                // via `status` — deleting would orphan real inventory/financial
                // records, not just a catalogue row.
                DeleteAction::make()
                    ->before(function (Branch $record, DeleteAction $action) {
                        $stockCount = $record->stocks()->where('quantity', '>', 0)->count();
                        $orderCount = $record->orders()->count();

                        if ($stockCount > 0 || $orderCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title("\"{$record->name}\" masih punya stok atau riwayat pesanan dan tidak bisa dihapus.")
                                ->body('Ubah statusnya menjadi Tutup Permanen.')
                                ->send();

                            $action->cancel();
                        }
                    }),
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

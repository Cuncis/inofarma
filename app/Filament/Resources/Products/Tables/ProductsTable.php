<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\AuditLogger;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['category', 'supplier'])
                ->withSum('stocks', 'quantity'))
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->alignRight()
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('stocks_sum_quantity')
                    ->label('Stok')
                    ->alignRight()
                    ->state(fn (Product $record) => (int) $record->stocks_sum_quantity)
                    ->badge()
                    ->color(fn (Product $record) => match (true) {
                        (int) $record->stocks_sum_quantity <= 0 => 'danger',
                        (int) $record->stocks_sum_quantity <= 20 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('sold_count')
                    ->label('Terjual')
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::PRODUCT_STATUSES, $state))
                    ->color(fn (string $state) => match ($state) {
                        'aktif' => 'success',
                        'arsip' => 'gray',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::PRODUCT_STATUSES)),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->after(fn (Product $record) => AuditLogger::log('produk_dihapus', null, ['name' => $record->name, 'sku' => $record->sku])),
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

<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Models\Product;
use App\Models\Supplier;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('products')
                ->addSelect(['revenue' => Product::query()
                    ->selectRaw('coalesce(sum(price * sold_count), 0)')
                    ->whereColumn('supplier_id', 'suppliers.id')
                    ->whereNull('deleted_at'),
                ]))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Pemasok')
                    ->searchable(),
                TextColumn::make('contact_person')
                    ->label('Pemilik')
                    ->searchable(),
                TextColumn::make('kota')
                    ->label('Kota')
                    ->searchable(),
                TextColumn::make('products_count')
                    ->label('Produk')
                    ->alignRight(),
                TextColumn::make('revenue')
                    ->label('Pendapatan')
                    ->alignRight()
                    ->formatStateUsing(fn (?string $state) => Money::rupiah((int) $state)),
                TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date('d M Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::SUPPLIER_STATUSES, $state)),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // Same guard as the legacy admin — `products.supplier_id` is
                // restrictOnDelete at the database level too, but the check
                // here is what gives a readable message instead of a raw
                // constraint-violation error.
                DeleteAction::make()
                    ->before(function (Supplier $record, DeleteAction $action) {
                        $count = $record->products()->count();

                        if ($count > 0) {
                            Notification::make()
                                ->danger()
                                ->title("\"{$record->name}\" masih memasok {$count} produk dan tidak bisa dihapus.")
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

<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
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

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('addresses')
                ->withCount('orders')
                ->withSum(
                    ['orders as spent_total' => fn ($query) => $query
                        ->whereNotIn('status', ['dibatalkan', 'kedaluwarsa']),
                    ],
                    'grand_total',
                ))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->state(fn (Customer $record) => ($record->addresses->firstWhere('is_default', true) ?? $record->addresses->first())?->kota),
                TextColumn::make('orders_count')
                    ->label('Pesanan')
                    ->alignRight(),
                TextColumn::make('spent_total')
                    ->label('Total Belanja')
                    ->alignRight()
                    ->formatStateUsing(fn (?string $state) => Money::rupiah((int) $state)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::CUSTOMER_STATUSES, $state)),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // Same guard as the legacy admin — `orders.customer_id` is
                // restrictOnDelete at the database level too; deactivate the
                // customer instead of deleting one with order history.
                DeleteAction::make()
                    ->before(function (Customer $record, DeleteAction $action) {
                        $count = $record->orders()->count();

                        if ($count > 0) {
                            Notification::make()
                                ->danger()
                                ->title("\"{$record->name}\" memiliki {$count} pesanan dan tidak bisa dihapus.")
                                ->body('Ubah statusnya menjadi Nonaktif.')
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

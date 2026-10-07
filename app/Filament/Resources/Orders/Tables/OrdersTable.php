<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['customer', 'branch'])
                ->withSum('items as item_count', 'quantity'))
            ->columns([
                TextColumn::make('number')
                    ->label(__('No. Pesanan'))
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label(__('Pelanggan'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Tanggal'))
                    ->date('d M Y'),
                TextColumn::make('item_count')
                    ->label(__('Item'))
                    ->alignRight(),
                TextColumn::make('payment_method')
                    ->label(__('Pembayaran'))
                    ->formatStateUsing(fn (?string $state) => $state ? __($state) : null),
                TextColumn::make('grand_total')
                    ->label(__('Total'))
                    ->alignRight()
                    ->weight('semibold')
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::ORDER_STATUSES, $state))
                    ->color(fn (string $state) => match ($state) {
                        'selesai' => 'success',
                        'diproses' => 'warning',
                        'siap diambil', 'dikirim' => 'info',
                        'dibatalkan', 'kedaluwarsa' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::ORDER_STATUSES)),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // A completed order is a financial record — see Order::getIsDeletableAttribute().
                DeleteAction::make()
                    ->before(function (Order $record, DeleteAction $action) {
                        if (! $record->is_deletable) {
                            Notification::make()
                                ->danger()
                                ->title(__('Pesanan #:number sudah selesai dan tidak bisa dihapus.', ['number' => $record->number]))
                                ->body(__('Ubah statusnya menjadi Dibatalkan bila perlu.'))
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

<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Order;
use App\Support\Money;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('customer'))
            ->columns([
                TextColumn::make('number')
                    ->label(__('Nomor'))
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label(__('Pelanggan'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Diterbitkan'))
                    ->date('d M Y'),
                TextColumn::make('expires_at')
                    ->label(__('Jatuh Tempo'))
                    ->date('d M Y')
                    ->placeholder('—'),
                TextColumn::make('grand_total')
                    ->label(__('Total'))
                    ->alignRight()
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->state(fn (Order $record) => self::status($record))
                    ->color(fn (string $state) => match ($state) {
                        'Lunas' => 'success',
                        'Refund' => 'gray',
                        'Jatuh Tempo' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    private static function status(Order $order): string
    {
        return match (true) {
            $order->payment_status === 'lunas' => __('Lunas'),
            $order->payment_status === 'refund' => __('Refund'),
            $order->expires_at?->isPast() => __('Jatuh Tempo'),
            default => __('Belum Bayar'),
        };
    }
}

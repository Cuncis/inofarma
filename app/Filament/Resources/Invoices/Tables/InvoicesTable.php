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
                    ->label('Nomor')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Diterbitkan')
                    ->date('d M Y'),
                TextColumn::make('expires_at')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->placeholder('—'),
                TextColumn::make('grand_total')
                    ->label('Total')
                    ->alignRight()
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('status')
                    ->label('Status')
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
            $order->payment_status === 'lunas' => 'Lunas',
            $order->payment_status === 'refund' => 'Refund',
            $order->expires_at?->isPast() => 'Jatuh Tempo',
            default => 'Belum Bayar',
        };
    }
}

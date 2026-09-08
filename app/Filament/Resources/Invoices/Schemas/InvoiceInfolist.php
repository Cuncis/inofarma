<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Order;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Faktur')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('number')
                                    ->label('Nomor'),
                                TextEntry::make('customer.name')
                                    ->label('Pelanggan'),
                                TextEntry::make('branch.name')
                                    ->label('Cabang'),
                                TextEntry::make('created_at')
                                    ->label('Diterbitkan')
                                    ->date('d M Y'),
                                TextEntry::make('expires_at')
                                    ->label('Jatuh Tempo')
                                    ->date('d M Y')
                                    ->placeholder('—'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->state(fn (Order $record) => self::status($record))
                                    ->color(fn (string $state) => match ($state) {
                                        'Lunas' => 'success',
                                        'Refund' => 'gray',
                                        'Jatuh Tempo' => 'danger',
                                        default => 'warning',
                                    }),
                            ]),
                    ]),
                Section::make('Rincian')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Item')
                            ->schema([
                                TextEntry::make('product_name')->label('Produk'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Harga')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ])
                            ->columns(3),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('discount_total')
                                    ->label('Diskon')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('shipping_total')
                                    ->label('Ongkir')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('tax_total')
                                    ->label('Pajak')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ]),
                        TextEntry::make('grand_total')
                            ->label('Total')
                            ->weight('bold')
                            ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                    ]),
                Section::make('Riwayat Pembayaran')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->label('')
                            ->schema([
                                TextEntry::make('invoice_number')->label('No. Invoice'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                                TextEntry::make('channel')->label('Kanal'),
                                TextEntry::make('amount')
                                    ->label('Jumlah')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d M Y, H:i'),
                                TextEntry::make('paid_at')
                                    ->label('Dibayar')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('—'),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn (Order $record) => $record->payments->isNotEmpty()),
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

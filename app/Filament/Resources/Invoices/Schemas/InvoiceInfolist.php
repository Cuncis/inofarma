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
                Section::make(__('Faktur'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('number')
                                    ->label(__('Nomor')),
                                TextEntry::make('customer.name')
                                    ->label(__('Pelanggan')),
                                TextEntry::make('branch.name')
                                    ->label(__('Cabang')),
                                TextEntry::make('created_at')
                                    ->label(__('Diterbitkan'))
                                    ->date('d M Y'),
                                TextEntry::make('expires_at')
                                    ->label(__('Jatuh Tempo'))
                                    ->date('d M Y')
                                    ->placeholder('—'),
                                TextEntry::make('status')
                                    ->label(__('Status'))
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
                Section::make(__('Rincian'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label(__('Item'))
                            ->schema([
                                TextEntry::make('product_name')->label(__('Produk')),
                                TextEntry::make('quantity')->label(__('Qty')),
                                TextEntry::make('unit_price')->label(__('Harga'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ])
                            ->columns(3),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('discount_total')
                                    ->label(__('Diskon'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('shipping_total')
                                    ->label(__('Ongkir'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('tax_total')
                                    ->label(__('Pajak'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ]),
                        TextEntry::make('grand_total')
                            ->label(__('Total'))
                            ->weight('bold')
                            ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                    ]),
                Section::make(__('Riwayat Pembayaran'))
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->label('')
                            ->schema([
                                TextEntry::make('invoice_number')->label(__('No. Invoice')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => __(ucfirst($state))),
                                TextEntry::make('channel')->label(__('Kanal')),
                                TextEntry::make('amount')
                                    ->label(__('Jumlah'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('created_at')
                                    ->label(__('Dibuat'))
                                    ->dateTime('d M Y, H:i'),
                                TextEntry::make('paid_at')
                                    ->label(__('Dibayar'))
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
            $order->payment_status === 'lunas' => __('Lunas'),
            $order->payment_status === 'refund' => __('Refund'),
            $order->expires_at?->isPast() => __('Jatuh Tempo'),
            default => __('Belum Bayar'),
        };
    }
}

<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('number')->label('No. Pesanan'),
                                TextEntry::make('customer.name')->label('Pelanggan'),
                                TextEntry::make('branch.name')->label('Cabang'),
                                TextEntry::make('fulfilment')
                                    ->label('Cara Terima')
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::FULFILMENTS, $state)),
                                TextEntry::make('payment_method')->label('Pembayaran'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::ORDER_STATUSES, $state)),
                                TextEntry::make('created_at')->label('Tanggal')->date('d M Y'),
                                TextEntry::make('note')->label('Catatan')->placeholder('—')->columnSpan(2),
                            ]),
                    ]),
                Section::make('Item Pesanan')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product_name')->label('Produk'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Harga')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('line_total')->label('Subtotal')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ])
                            ->columns(4),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('shipping_total')
                                    ->label('Ongkos Kirim')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('grand_total')
                                    ->label('Total')
                                    ->weight('bold')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ]),
                    ]),
                Section::make('Pengiriman')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('shipment.courier_name')->label('Kurir')->placeholder('—'),
                                TextEntry::make('shipment.courier_service_name')->label('Layanan')->placeholder('—'),
                                TextEntry::make('shipment.waybill_id')->label('No. Resi')->placeholder('Belum dibuat'),
                                TextEntry::make('shipment.status')->label('Status Kirim')->placeholder('—'),
                            ]),
                    ])
                    ->visible(fn (Order $record) => $record->fulfilment === 'antar' && $record->shipment !== null),
                Section::make('Pengambilan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('pickup_code')->label('Kode Ambil')->placeholder('Belum diterbitkan'),
                                TextEntry::make('pickup_code_expires_at')->label('Berlaku Sampai')
                                    ->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('picked_up_at')->label('Diambil Pada')
                                    ->dateTime('d M Y, H:i')->placeholder('Belum diambil'),
                            ]),
                    ])
                    ->visible(fn (Order $record) => $record->fulfilment === 'ambil'),
            ]);
    }
}

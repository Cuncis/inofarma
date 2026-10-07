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
                Section::make(__('Ringkasan'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('number')->label(__('No. Pesanan')),
                                TextEntry::make('customer.name')->label(__('Pelanggan')),
                                TextEntry::make('branch.name')->label(__('Cabang')),
                                TextEntry::make('fulfilment')
                                    ->label(__('Cara Terima'))
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::FULFILMENTS, $state)),
                                TextEntry::make('payment_method')->label(__('Pembayaran'))->formatStateUsing(fn (?string $state) => $state ? __($state) : null),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::ORDER_STATUSES, $state)),
                                TextEntry::make('created_at')->label(__('Tanggal'))->date('d M Y'),
                                TextEntry::make('note')->label(__('Catatan'))->placeholder('—')->columnSpan(2),
                            ]),
                    ]),
                Section::make(__('Item Pesanan'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product_name')->label(__('Produk')),
                                TextEntry::make('quantity')->label(__('Qty')),
                                TextEntry::make('unit_price')->label(__('Harga'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('line_total')->label(__('Subtotal'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ])
                            ->columns(4),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('shipping_total')
                                    ->label(__('Ongkos Kirim'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('grand_total')
                                    ->label(__('Total'))
                                    ->weight('bold')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                            ]),
                    ]),
                Section::make(__('Pengiriman'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('shipment.courier_name')->label(__('Kurir'))->placeholder('—'),
                                TextEntry::make('shipment.courier_service_name')->label(__('Layanan'))->placeholder('—'),
                                TextEntry::make('shipment.waybill_id')->label(__('No. Resi'))->placeholder(__('Belum dibuat')),
                                TextEntry::make('shipment.status')->label(__('Status Kirim'))->placeholder('—'),
                            ]),
                    ])
                    ->visible(fn (Order $record) => $record->fulfilment === 'antar' && $record->shipment !== null),
                Section::make(__('Pengambilan'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('pickup_code')->label(__('Kode Ambil'))->placeholder(__('Belum diterbitkan')),
                                TextEntry::make('pickup_code_expires_at')->label(__('Berlaku Sampai'))
                                    ->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('picked_up_at')->label(__('Diambil Pada'))
                                    ->dateTime('d M Y, H:i')->placeholder(__('Belum diambil')),
                            ]),
                    ])
                    ->visible(fn (Order $record) => $record->fulfilment === 'ambil'),
            ]);
    }
}

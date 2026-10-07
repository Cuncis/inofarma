<?php

namespace App\Filament\Resources\StockTransfers\Schemas;

use App\Support\AdminOptions;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockTransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Transfer'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')->label(__('Kode')),
                                TextEntry::make('fromBranch.name')->label(__('Dari')),
                                TextEntry::make('toBranch.name')->label(__('Ke')),
                                TextEntry::make('product.name')->label(__('Produk')),
                                TextEntry::make('quantity')->label(__('Jumlah')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::STOCK_TRANSFER_STATUSES, $state)),
                                TextEntry::make('requestedBy.name')->label(__('Diminta Oleh'))->placeholder('—'),
                                TextEntry::make('shipped_at')->label(__('Dikirim Pada'))->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('received_at')->label(__('Diterima Pada'))->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('note')->label(__('Catatan'))->placeholder('—')->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}

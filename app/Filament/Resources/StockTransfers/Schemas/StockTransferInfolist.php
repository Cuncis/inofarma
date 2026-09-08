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
                Section::make('Transfer')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')->label('Kode'),
                                TextEntry::make('fromBranch.name')->label('Dari'),
                                TextEntry::make('toBranch.name')->label('Ke'),
                                TextEntry::make('product.name')->label('Produk'),
                                TextEntry::make('quantity')->label('Jumlah'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::STOCK_TRANSFER_STATUSES, $state)),
                                TextEntry::make('requestedBy.name')->label('Diminta Oleh')->placeholder('—'),
                                TextEntry::make('shipped_at')->label('Dikirim Pada')->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('received_at')->label('Diterima Pada')->dateTime('d M Y, H:i')->placeholder('—'),
                                TextEntry::make('note')->label('Catatan')->placeholder('—')->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}

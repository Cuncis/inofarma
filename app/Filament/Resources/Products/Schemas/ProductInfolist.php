<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Produk')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sku')->label('SKU'),
                                TextEntry::make('name')->label('Nama'),
                                TextEntry::make('category.name')->label('Kategori'),
                                TextEntry::make('supplier.name')->label('Penjual'),
                                TextEntry::make('price')->label('Harga')
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('sold_count')->label('Terjual'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::PRODUCT_STATUSES, $state)),
                                TextEntry::make('drug_class')
                                    ->label('Golongan Obat')
                                    ->formatStateUsing(fn (?string $state) => $state ? AdminOptions::toLabel(AdminOptions::DRUG_CLASSES, $state) : '—'),
                                TextEntry::make('needs_warning_label')
                                    ->label('Wajib Label Peringatan')
                                    ->formatStateUsing(fn (bool $state) => $state ? 'Ya' : 'Tidak'),
                            ]),
                    ]),
                Section::make('Stok per Cabang')
                    ->schema([
                        RepeatableEntry::make('stocks')
                            ->label('')
                            ->schema([
                                TextEntry::make('branch.name')->label('Cabang'),
                                TextEntry::make('quantity')->label('Jumlah'),
                                TextEntry::make('reserved_quantity')->label('Dipesan'),
                                TextEntry::make('available')->label('Tersedia'),
                            ])
                            ->columns(4),
                    ])
                    ->visible(fn (Product $record) => $record->stocks->isNotEmpty()),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Produk'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sku')->label(__('SKU')),
                                TextEntry::make('name')->label(__('Nama')),
                                TextEntry::make('category.name')->label(__('Kategori')),
                                TextEntry::make('supplier.name')->label(__('Penjual')),
                                TextEntry::make('price')->label(__('Harga'))
                                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                                TextEntry::make('sold_count')->label(__('Terjual')),
                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::PRODUCT_STATUSES, $state)),
                                TextEntry::make('drug_class')
                                    ->label(__('Golongan Obat'))
                                    ->formatStateUsing(fn (?string $state) => $state ? AdminOptions::toLabel(AdminOptions::DRUG_CLASSES, $state) : '—'),
                                TextEntry::make('needs_warning_label')
                                    ->label(__('Wajib Label Peringatan'))
                                    ->formatStateUsing(fn (bool $state) => $state ? __('Ya') : __('Tidak')),
                            ]),
                    ]),
                // The photos sit right under the product details, above the stock. The
                // same table the Gambar tab shows on the edit page, so uploading,
                // choosing the main photo, reordering and deleting all work here too.
                Livewire::make(
                    ImagesRelationManager::class,
                    fn (Product $record, $livewire): array => ['ownerRecord' => $record, 'pageClass' => $livewire::class],
                )
                    ->key('product-images')
                    ->columnSpanFull(),
                Section::make(__('Stok per Cabang'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('stocks')
                            ->label('')
                            ->schema([
                                TextEntry::make('branch.name')->label(__('Cabang')),
                                TextEntry::make('quantity')->label(__('Jumlah')),
                                TextEntry::make('reserved_quantity')->label(__('Dipesan')),
                                TextEntry::make('available')->label(__('Tersedia')),
                            ])
                            ->columns(4),
                    ])
                    ->visible(fn (Product $record) => $record->stocks->isNotEmpty()),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Informasi Umum'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nama Produk'))
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label(__('Kategori'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->label(__('Penjual'))
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('unit')
                            ->label(__('Satuan'))
                            ->options(AdminOptions::listOptions(AdminOptions::units()))
                            ->required(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(AdminOptions::options(AdminOptions::PRODUCT_STATUSES))
                            ->required(),
                        Select::make('drug_class')
                            ->label(__('Golongan Obat'))
                            ->options(AdminOptions::options(AdminOptions::SELLABLE_DRUG_CLASSES))
                            ->default('bebas')
                            ->helperText(__('Apotek ini tidak menjual obat keras atau obat yang butuh resep dokter.'))
                            ->required(),
                        TextInput::make('price')
                            ->label(__('Harga Jual'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000)
                            ->required(),
                        TextInput::make('old_price')
                            ->label(__('Harga Coret'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000)
                            ->gt('price')
                            ->validationMessages(['gt' => __('Harga coret harus lebih besar dari harga jual.')]),
                        FileUpload::make('photos')
                            ->label(__('Foto Produk'))
                            ->helperText(__('Opsional. Foto pertama menjadi foto utama. Foto bisa ditambah atau diurutkan lagi nanti di tab Gambar.'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(8)
                            ->maxSize(5120)
                            ->storeFiles(false)
                            ->visibleOn('create')
                            ->columnSpanFull(),
                        Textarea::make('blurb')
                            ->label(__('Deskripsi'))
                            ->helperText(__('Tulis komposisi, indikasi, aturan pakai, efek samping, produsen, nomor izin edar, dan kondisi penyimpanan di sini.'))
                            ->rows(10)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

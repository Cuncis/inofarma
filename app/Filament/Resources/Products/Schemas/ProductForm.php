<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Informasi Umum'))
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
                        Toggle::make('requires_prescription')
                            ->label(__('Wajib Resep')),
                        Textarea::make('blurb')
                            ->label(__('Deskripsi Singkat'))
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Data Farmasi'))
                    ->columns(2)
                    ->schema([
                        Select::make('drug_class')
                            ->label(__('Golongan Obat'))
                            ->options(AdminOptions::options(AdminOptions::DRUG_CLASSES))
                            ->default('non-obat')
                            ->live(),
                        TextInput::make('nie_bpom')
                            ->label(__('Nomor Izin Edar'))
                            ->maxLength(40),
                        TextInput::make('composition')
                            ->label(__('Komposisi'))
                            ->maxLength(255),
                        Textarea::make('indication')
                            ->label(__('Indikasi'))
                            ->maxLength(1000),
                        Textarea::make('dosage')
                            ->label(__('Aturan Pakai'))
                            ->maxLength(1000),
                        Textarea::make('side_effects')
                            ->label(__('Efek Samping'))
                            ->maxLength(1000),
                        Textarea::make('warning')
                            ->label(__('Peringatan'))
                            ->maxLength(1000)
                            ->required(fn (Get $get) => $get('drug_class') === 'bebas terbatas')
                            ->validationMessages(['required' => __('Obat bebas terbatas wajib menampilkan peringatan P1–P6.')])
                            ->columnSpanFull(),
                        TextInput::make('manufacturer')
                            ->label(__('Produsen'))
                            ->maxLength(120),
                        TextInput::make('max_qty_per_order')
                            ->label(__('Batas Pembelian'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(1000),
                        Select::make('storage')
                            ->label(__('Kondisi Penyimpanan'))
                            ->options(AdminOptions::options(AdminOptions::STORAGE_CONDITIONS))
                            ->default('suhu ruang'),
                        TextInput::make('weight_grams')
                            ->label(__('Berat (gram)'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100000)
                            ->default(0),
                        TextInput::make('length_cm')
                            ->label(__('Panjang (cm)'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                        TextInput::make('width_cm')
                            ->label(__('Lebar (cm)'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                        TextInput::make('height_cm')
                            ->label(__('Tinggi (cm)'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                    ]),
            ]);
    }
}

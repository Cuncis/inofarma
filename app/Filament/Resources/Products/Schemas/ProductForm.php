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
                Section::make('Informasi Umum')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Penjual')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('unit')
                            ->label('Satuan')
                            ->options(array_combine(AdminOptions::units(), AdminOptions::units()))
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(array_flip(AdminOptions::PRODUCT_STATUSES))
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga Jual')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000)
                            ->required(),
                        TextInput::make('old_price')
                            ->label('Harga Coret')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000)
                            ->gt('price')
                            ->validationMessages(['gt' => 'Harga coret harus lebih besar dari harga jual.']),
                        Toggle::make('requires_prescription')
                            ->label('Wajib Resep'),
                        Textarea::make('blurb')
                            ->label('Deskripsi Singkat')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Data Farmasi')
                    ->columns(2)
                    ->schema([
                        Select::make('drug_class')
                            ->label('Golongan Obat')
                            ->options(array_flip(AdminOptions::DRUG_CLASSES))
                            ->default('non-obat')
                            ->live(),
                        TextInput::make('nie_bpom')
                            ->label('Nomor Izin Edar')
                            ->maxLength(40),
                        TextInput::make('composition')
                            ->label('Komposisi')
                            ->maxLength(255),
                        Textarea::make('indication')
                            ->label('Indikasi')
                            ->maxLength(1000),
                        Textarea::make('dosage')
                            ->label('Aturan Pakai')
                            ->maxLength(1000),
                        Textarea::make('side_effects')
                            ->label('Efek Samping')
                            ->maxLength(1000),
                        Textarea::make('warning')
                            ->label('Peringatan')
                            ->maxLength(1000)
                            ->required(fn (Get $get) => $get('drug_class') === 'bebas terbatas')
                            ->validationMessages(['required' => 'Obat bebas terbatas wajib menampilkan peringatan P1–P6.'])
                            ->columnSpanFull(),
                        TextInput::make('manufacturer')
                            ->label('Produsen')
                            ->maxLength(120),
                        TextInput::make('max_qty_per_order')
                            ->label('Batas Pembelian')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(1000),
                        Select::make('storage')
                            ->label('Kondisi Penyimpanan')
                            ->options(array_flip(AdminOptions::STORAGE_CONDITIONS))
                            ->default('suhu ruang'),
                        TextInput::make('weight_grams')
                            ->label('Berat (gram)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100000)
                            ->default(0),
                        TextInput::make('length_cm')
                            ->label('Panjang (cm)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                        TextInput::make('width_cm')
                            ->label('Lebar (cm)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                        TextInput::make('height_cm')
                            ->label('Tinggi (cm)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0),
                    ]),
            ]);
    }
}

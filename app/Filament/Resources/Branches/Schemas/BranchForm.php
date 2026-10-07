<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identitas Cabang'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Nama Cabang'))
                            ->placeholder(__('Apotek Inofarma Cinere'))
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(AdminOptions::options(AdminOptions::BRANCH_STATUSES))
                            ->default('aktif')
                            ->required(),
                        TextInput::make('phone')
                            ->label(__('Telepon'))
                            ->tel()
                            ->placeholder('+62 21 5551 0001')
                            ->maxLength(30),
                        TextInput::make('whatsapp')
                            ->label(__('WhatsApp'))
                            ->tel()
                            ->placeholder('+62 812-0000-1111')
                            ->maxLength(30),
                    ]),

                Section::make(__('Alamat & Lokasi'))
                    ->columns(2)
                    ->components([
                        TextInput::make('address_line')
                            ->label(__('Alamat Jalan'))
                            ->placeholder(__('Jl. Contoh No. 1'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('kelurahan')->maxLength(80),
                        TextInput::make('kecamatan')->maxLength(80),
                        TextInput::make('kota')
                            ->label(__('Kota/Kabupaten'))
                            ->required()
                            ->maxLength(80),
                        TextInput::make('provinsi')
                            ->label(__('Provinsi'))
                            ->required()
                            ->maxLength(80),
                        TextInput::make('postal_code')
                            ->label(__('Kode Pos'))
                            ->maxLength(10),
                        TextInput::make('latitude')
                            ->label(__('Lintang (Latitude)'))
                            ->numeric()
                            ->placeholder('-6.200000')
                            ->minValue(-90)
                            ->maxValue(90)
                            ->helperText(__('Kosongkan bila belum diketahui — cabang tidak akan muncul di pencarian terdekat.')),
                        TextInput::make('longitude')
                            ->label(__('Bujur (Longitude)'))
                            ->numeric()
                            ->placeholder('106.816666')
                            ->minValue(-180)
                            ->maxValue(180),
                    ]),

                Section::make(__('Perizinan'))
                    ->columns(2)
                    ->components([
                        TextInput::make('sia_number')
                            ->label(__('Nomor SIA'))
                            ->placeholder('SIA/2025/00123')
                            ->maxLength(60),
                        TextInput::make('apj_name')
                            ->label(__('Nama APJ'))
                            ->maxLength(120),
                        TextInput::make('apj_sipa_number')
                            ->label(__('Nomor SIPA APJ'))
                            ->maxLength(60),
                    ]),

                Section::make(__('Layanan'))
                    ->columns(2)
                    ->components([
                        Toggle::make('supports_delivery')
                            ->label(__('Melayani antar'))
                            ->default(true)
                            ->required(),
                        Toggle::make('supports_pickup')
                            ->label(__('Melayani ambil di tempat'))
                            ->default(true)
                            ->required(),
                        TextInput::make('delivery_radius_km')
                            ->label(__('Radius Antar (km)'))
                            ->numeric()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                    ]),
            ]);
    }
}

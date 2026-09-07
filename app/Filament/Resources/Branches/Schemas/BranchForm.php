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
                Section::make('Identitas Cabang')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Cabang')
                            ->placeholder('Apotek Inofarma Cinere')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options(array_flip(AdminOptions::BRANCH_STATUSES))
                            ->default('aktif')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->placeholder('+62 21 5551 0001')
                            ->maxLength(30),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->placeholder('+62 812-0000-1111')
                            ->maxLength(30),
                    ]),

                Section::make('Alamat & Lokasi')
                    ->columns(2)
                    ->components([
                        TextInput::make('address_line')
                            ->label('Alamat Jalan')
                            ->placeholder('Jl. Contoh No. 1')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('kelurahan')->maxLength(80),
                        TextInput::make('kecamatan')->maxLength(80),
                        TextInput::make('kota')
                            ->label('Kota/Kabupaten')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('provinsi')
                            ->label('Provinsi')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->maxLength(10),
                        TextInput::make('latitude')
                            ->label('Lintang (Latitude)')
                            ->numeric()
                            ->placeholder('-6.200000')
                            ->minValue(-90)
                            ->maxValue(90)
                            ->helperText('Kosongkan bila belum diketahui — cabang tidak akan muncul di pencarian terdekat.'),
                        TextInput::make('longitude')
                            ->label('Bujur (Longitude)')
                            ->numeric()
                            ->placeholder('106.816666')
                            ->minValue(-180)
                            ->maxValue(180),
                    ]),

                Section::make('Perizinan')
                    ->columns(2)
                    ->components([
                        TextInput::make('sia_number')
                            ->label('Nomor SIA')
                            ->placeholder('SIA/2025/00123')
                            ->maxLength(60),
                        TextInput::make('apj_name')
                            ->label('Nama APJ')
                            ->maxLength(120),
                        TextInput::make('apj_sipa_number')
                            ->label('Nomor SIPA APJ')
                            ->maxLength(60),
                    ]),

                Section::make('Layanan')
                    ->columns(2)
                    ->components([
                        Toggle::make('supports_delivery')
                            ->label('Melayani antar')
                            ->default(true)
                            ->required(),
                        Toggle::make('supports_pickup')
                            ->label('Melayani ambil di tempat')
                            ->default(true)
                            ->required(),
                        TextInput::make('delivery_radius_km')
                            ->label('Radius Antar (km)')
                            ->numeric()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                    ]),
            ]);
    }
}

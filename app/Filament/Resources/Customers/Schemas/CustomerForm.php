<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->placeholder('Kirana Wijaya')
                    ->required()
                    ->maxLength(80),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->placeholder('kirana@mail.com')
                    ->helperText('Dipakai untuk menautkan riwayat pesanan.')
                    ->required()
                    ->maxLength(120)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Email ini sudah dipakai pelanggan lain.']),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->placeholder('+62 812-3456-7890')
                    ->required()
                    ->maxLength(30)
                    ->regex('/^[0-9+\-\s()]+$/')
                    ->validationMessages(['regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).']),
                // `city`/`address` are not Customer columns — they read from
                // and write back to the default `CustomerAddress`, same as
                // CustomerController::syncAddress().
                TextInput::make('city')
                    ->label('Kota')
                    ->placeholder('Jakarta Barat')
                    ->required()
                    ->maxLength(60),
                Select::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::CUSTOMER_STATUSES))
                    ->default('aktif')
                    ->required(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->placeholder('Jl. Kebon Jeruk Raya No. 27')
                    ->columnSpanFull(),
            ]);
    }
}

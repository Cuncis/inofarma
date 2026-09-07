<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Toko')
                    ->placeholder('Apotek Sehat Bersama')
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Nama toko ini sudah terdaftar.'])
                    ->helperText(function (?Supplier $record): ?string {
                        $count = $record?->products()->count() ?? 0;

                        return $count > 0
                            ? "Mengubah nama toko akan otomatis memperbarui {$count} produk yang dipasoknya."
                            : null;
                    }),
                TextInput::make('contact_person')
                    ->label('Nama Pemilik')
                    ->placeholder('Kirana Wijaya')
                    ->required()
                    ->maxLength(80),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->placeholder('apotek@mail.com')
                    ->required()
                    ->maxLength(120)
                    ->unique(table: Supplier::class, ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Email ini sudah dipakai pemasok lain.']),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->placeholder('+62 21 5551 0001')
                    ->required()
                    ->maxLength(30)
                    ->regex('/^[0-9+\-\s()]+$/')
                    ->validationMessages(['regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).']),
                TextInput::make('license_number')
                    ->label('Nomor Izin Apotek')
                    ->placeholder('SIA/2025/00123')
                    ->helperText('Harus unik untuk setiap pemasok.')
                    ->required()
                    ->maxLength(40)
                    ->unique(table: Supplier::class, ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Nomor izin apotek ini sudah terdaftar.']),
                TextInput::make('kota')
                    ->label('Kota')
                    ->placeholder('Jakarta Selatan')
                    ->required()
                    ->maxLength(60),
                Select::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::SUPPLIER_STATUSES))
                    ->default('aktif')
                    ->required(),
                Textarea::make('address_line')
                    ->label('Alamat Toko')
                    ->placeholder('Jl. Jend. Sudirman Kav. 52-53')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}

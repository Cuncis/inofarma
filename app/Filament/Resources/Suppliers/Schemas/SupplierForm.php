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
                    ->label(__('Nama Toko'))
                    ->placeholder(__('Apotek Sehat Bersama'))
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => __('Nama toko ini sudah terdaftar.')])
                    ->helperText(function (?Supplier $record): ?string {
                        $count = $record?->products()->count() ?? 0;

                        return $count > 0
                            ? __('Mengubah nama toko akan otomatis memperbarui :count produk yang dipasoknya.', ['count' => $count])
                            : null;
                    }),
                TextInput::make('contact_person')
                    ->label(__('Nama Pemilik'))
                    ->placeholder(__('Kirana Wijaya'))
                    ->required()
                    ->maxLength(80),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->placeholder(__('apotek@mail.com'))
                    ->required()
                    ->maxLength(120)
                    ->unique(table: Supplier::class, ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => __('Email ini sudah dipakai pemasok lain.')]),
                TextInput::make('phone')
                    ->label(__('Nomor Telepon'))
                    ->tel()
                    ->placeholder('+62 21 5551 0001')
                    ->required()
                    ->maxLength(30)
                    ->regex('/^[0-9+\-\s()]+$/')
                    ->validationMessages(['regex' => __('Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).')]),
                TextInput::make('license_number')
                    ->label(__('Nomor Izin Apotek'))
                    ->placeholder('SIA/2025/00123')
                    ->helperText(__('Harus unik untuk setiap pemasok.'))
                    ->required()
                    ->maxLength(40)
                    ->unique(table: Supplier::class, ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => __('Nomor izin apotek ini sudah terdaftar.')]),
                TextInput::make('kota')
                    ->label(__('Kota'))
                    ->placeholder(__('Jakarta Selatan'))
                    ->required()
                    ->maxLength(60),
                Select::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::SUPPLIER_STATUSES))
                    ->default('aktif')
                    ->required(),
                Textarea::make('address_line')
                    ->label(__('Alamat Toko'))
                    ->placeholder(__('Jl. Jend. Sudirman Kav. 52-53'))
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}

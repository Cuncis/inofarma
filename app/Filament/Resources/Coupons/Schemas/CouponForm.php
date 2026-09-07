<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('code')
                    ->label('Kode Kupon')
                    ->placeholder('HEMAT15')
                    ->helperText('Huruf kapital dan angka saja.')
                    ->required()
                    ->maxLength(40)
                    ->regex('/^[A-Z0-9]+$/')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::upper((string) $state)))
                    ->dehydrateStateUsing(fn ($state) => Str::upper((string) $state))
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages([
                        'unique' => 'Kode kupon ini sudah dipakai.',
                        'regex' => 'Kode kupon hanya boleh berisi huruf kapital dan angka.',
                    ]),
                Select::make('type')
                    ->label('Tipe Diskon')
                    ->options(array_flip(AdminOptions::COUPON_TYPES))
                    ->default('persentase')
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label(fn (Get $get) => $get('type') === 'persentase' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)')
                    ->placeholder('15')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1_000_000_000)
                    ->visible(fn (Get $get) => $get('type') !== 'ongkir gratis')
                    ->required(fn (Get $get) => $get('type') !== 'ongkir gratis')
                    ->validationMessages(['required' => 'Nilai kupon wajib diisi untuk tipe ini.']),
                TextInput::make('minimum_purchase')
                    ->label('Minimum Belanja (Rp)')
                    ->placeholder('100000')
                    ->helperText('Kosongkan bila tidak ada.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1_000_000_000),
                TextInput::make('quota')
                    ->label('Kuota Penggunaan')
                    ->placeholder('500')
                    ->helperText('Kosongkan bila tidak dibatasi.')
                    ->numeric()
                    ->minValue(1),
                Select::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::COUPON_STATUSES))
                    ->default('aktif')
                    ->required(),
                DatePicker::make('starts_at')
                    ->label('Mulai Berlaku'),
                DatePicker::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->afterOrEqual('starts_at')
                    ->validationMessages(['after_or_equal' => 'Tanggal berlaku sampai tidak boleh sebelum tanggal mulai.']),
                CheckboxList::make('branches')
                    ->label('Berlaku di Cabang')
                    ->helperText('Tidak mencentang cabang mana pun berarti berlaku di semua cabang.')
                    ->relationship('branches', 'name')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

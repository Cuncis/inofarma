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
                    ->label(__('Kode Kupon'))
                    ->placeholder('HEMAT15')
                    ->helperText(__('Huruf kapital dan angka saja.'))
                    ->required()
                    ->maxLength(40)
                    ->regex('/^[A-Z0-9]+$/')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::upper((string) $state)))
                    ->dehydrateStateUsing(fn ($state) => Str::upper((string) $state))
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages([
                        'unique' => __('Kode kupon ini sudah dipakai.'),
                        'regex' => __('Kode kupon hanya boleh berisi huruf kapital dan angka.'),
                    ]),
                Select::make('type')
                    ->label(__('Tipe Diskon'))
                    ->options(AdminOptions::options(AdminOptions::COUPON_TYPES))
                    ->default('persentase')
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label(fn (Get $get) => $get('type') === 'persentase' ? __('Nilai Diskon (%)') : __('Nilai Diskon (Rp)'))
                    ->placeholder('15')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1_000_000_000)
                    ->visible(fn (Get $get) => $get('type') !== 'ongkir gratis')
                    ->required(fn (Get $get) => $get('type') !== 'ongkir gratis')
                    ->validationMessages(['required' => __('Nilai kupon wajib diisi untuk tipe ini.')]),
                TextInput::make('minimum_purchase')
                    ->label(__('Minimum Belanja (Rp)'))
                    ->placeholder('100000')
                    ->helperText(__('Kosongkan bila tidak ada.'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1_000_000_000),
                TextInput::make('quota')
                    ->label(__('Kuota Penggunaan'))
                    ->placeholder('500')
                    ->helperText(__('Kosongkan bila tidak dibatasi.'))
                    ->numeric()
                    ->minValue(1),
                Select::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::COUPON_STATUSES))
                    ->default('aktif')
                    ->required(),
                DatePicker::make('starts_at')
                    ->label(__('Mulai Berlaku')),
                DatePicker::make('expires_at')
                    ->label(__('Berlaku Sampai'))
                    ->afterOrEqual('starts_at')
                    ->validationMessages(['after_or_equal' => __('Tanggal berlaku sampai tidak boleh sebelum tanggal mulai.')]),
                CheckboxList::make('branches')
                    ->label(__('Berlaku di Cabang'))
                    ->helperText(__('Tidak mencentang cabang mana pun berarti berlaku di semua cabang.'))
                    ->relationship('branches', 'name')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

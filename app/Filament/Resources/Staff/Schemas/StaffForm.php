<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Akun'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nama'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->label(__('Telepon'))
                            ->maxLength(30),
                        Select::make('branch_id')
                            ->label(__('Cabang'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder(__('Pusat (semua cabang)')),
                        TextInput::make('password')
                            ->label(__('Kata Sandi'))
                            ->password()
                            ->revealable()
                            ->rule(Password::defaults())
                            ->confirmed()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->dehydrateStateUsing(fn (string $state) => Hash::make($state)),
                        TextInput::make('password_confirmation')
                            ->label(__('Konfirmasi Kata Sandi'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(false),
                        Toggle::make('is_active')
                            ->label(__('Aktif'))
                            ->default(true),
                    ]),
                Section::make(__('Peran'))
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('')
                            ->relationship('roles', 'name')
                            ->columns(3)
                            ->bulkToggleable(),
                    ]),
            ]);
    }
}

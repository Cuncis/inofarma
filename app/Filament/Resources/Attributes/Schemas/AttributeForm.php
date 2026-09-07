<?php

namespace App\Filament\Resources\Attributes\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Atribut')
                    ->placeholder('Bentuk Sediaan')
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Atribut dengan nama ini sudah ada.']),
                Select::make('type')
                    ->label('Tipe')
                    ->options(array_flip(AdminOptions::ATTRIBUTE_TYPES))
                    ->default('pilihan')
                    ->required()
                    ->live(),
                TagsInput::make('values')
                    ->label('Nilai')
                    ->placeholder('Tablet, Kapsul, Sirup, Salep')
                    ->helperText('Tekan Enter setelah setiap nilai.')
                    ->visible(fn (Get $get) => $get('type') === 'pilihan')
                    ->required(fn (Get $get) => $get('type') === 'pilihan')
                    ->distinct()
                    ->splitKeys([','])
                    ->columnSpanFull(),
            ]);
    }
}

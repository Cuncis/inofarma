<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\AdminOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => 'Kategori dengan nama ini sudah ada.']),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(80)
                    ->regex('/^[a-z0-9-]+$/')
                    ->validationMessages(['regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.'])
                    ->helperText('Kosongkan untuk membuat otomatis dari nama.'),
                Select::make('status')
                    ->label('Status')
                    ->options(array_flip(AdminOptions::CATEGORY_STATUSES))
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}

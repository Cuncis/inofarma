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
                    ->label(__('Nama Kategori'))
                    ->required()
                    ->maxLength(80)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                    ->validationMessages(['unique' => __('Kategori dengan nama ini sudah ada.')]),
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->maxLength(80)
                    ->regex('/^[a-z0-9-]+$/')
                    ->validationMessages(['regex' => __('Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.')])
                    ->helperText(__('Kosongkan untuk membuat otomatis dari nama.')),
                Select::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::CATEGORY_STATUSES))
                    ->required(),
                Textarea::make('description')
                    ->label(__('Deskripsi'))
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}

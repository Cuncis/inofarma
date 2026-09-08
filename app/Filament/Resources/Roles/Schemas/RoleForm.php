<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Peran')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100)
                            ->unique(table: Role::class, ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(255),
                    ]),
                Section::make('Hak Akses')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3),
                    ]),
            ]);
    }
}

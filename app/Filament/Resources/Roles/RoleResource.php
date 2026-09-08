<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Tables\RolesTable;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Peran CRUD — see RoleController's docblock. A role still assigned to staff
 * cannot be deleted; it forces an explicit reassignment first.
 */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Peran';

    protected static ?string $pluralModelLabel = 'Peran';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'peran';

    public static function canViewAny(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Peran:Lihat');
    }

    public static function canCreate(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Peran:Ubah');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Peran:Ubah');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Peran:Ubah');
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}

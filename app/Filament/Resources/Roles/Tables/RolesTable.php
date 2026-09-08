<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\Role;
use App\Support\AuditLogger;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['users', 'permissions']))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->placeholder('—')
                    ->limit(60),
                TextColumn::make('users_count')
                    ->label('Staf')
                    ->alignRight(),
                TextColumn::make('permissions_count')
                    ->label('Hak Akses')
                    ->alignRight(),
            ])
            ->recordActions([
                EditAction::make(),
                // A role still assigned to staff forces an explicit
                // reassignment first — see RoleController::destroy().
                DeleteAction::make()
                    ->before(function (Role $record, DeleteAction $action) {
                        $count = $record->users()->count();

                        if ($count > 0) {
                            Notification::make()
                                ->danger()
                                ->title("Peran \"{$record->name}\" masih dipakai {$count} staf dan tidak bisa dihapus.")
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->after(fn (Role $record) => AuditLogger::log('role_deleted', null, ['name' => $record->name])),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use App\Support\AuditLogger;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in RolesTable.
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
        ];
    }

    protected function afterSave(): void
    {
        AuditLogger::log('role_updated', $this->record, [], [
            'name' => $this->record->name,
            'permissions' => $this->record->permissions()->pluck('name')->all(),
        ]);
    }
}

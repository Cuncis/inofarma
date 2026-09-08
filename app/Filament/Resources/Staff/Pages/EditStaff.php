<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use App\Support\AuditLogger;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in StaffTable.
            DeleteAction::make()
                ->before(function (User $record, DeleteAction $action) {
                    if ($record->id === Auth::guard('web')->id()) {
                        Notification::make()
                            ->danger()
                            ->title('Anda tidak bisa menghapus akun Anda sendiri.')
                            ->send();

                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        AuditLogger::log('staff_updated', $this->record, [], [
            'email' => $this->record->email,
            'roles' => $this->record->roles()->pluck('name')->all(),
        ]);
    }
}

<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Support\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('staff_created', $this->record, [], [
            'email' => $this->record->email,
            'roles' => $this->record->roles()->pluck('name')->all(),
        ]);
    }
}

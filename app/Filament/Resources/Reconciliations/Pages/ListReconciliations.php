<?php

namespace App\Filament\Resources\Reconciliations\Pages;

use App\Filament\Resources\Reconciliations\ReconciliationResource;
use App\Filament\Widgets\DailyReconciliationWidget;
use Filament\Resources\Pages\ListRecords;

class ListReconciliations extends ListRecords
{
    protected static string $resource = ReconciliationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DailyReconciliationWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Read-only — a payment attempt is never created here.
        ];
    }
}

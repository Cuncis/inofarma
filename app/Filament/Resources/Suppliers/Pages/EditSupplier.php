<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in SuppliersTable.
            DeleteAction::make()
                ->before(function (Supplier $record, DeleteAction $action) {
                    $count = $record->products()->count();

                    if ($count > 0) {
                        Notification::make()
                            ->danger()
                            ->title("\"{$record->name}\" masih memasok {$count} produk dan tidak bisa dihapus.")
                            ->send();

                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in OrdersTable.
            DeleteAction::make()
                ->before(function (Order $record, DeleteAction $action) {
                    if (! $record->is_deletable) {
                        Notification::make()
                            ->danger()
                            ->title("Pesanan #{$record->number} sudah selesai dan tidak bisa dihapus.")
                            ->body('Ubah statusnya menjadi Dibatalkan bila perlu.')
                            ->send();

                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['payment_status'] = $data['status'] === 'selesai' ? 'lunas' : 'belum bayar';

        return $data;
    }

    /**
     * The Repeater's own relationship save persists the line items after the
     * order is saved — recompute the money columns from what actually
     * landed, same as OrderController::syncItems().
     */
    protected function afterSave(): void
    {
        $subtotal = (int) $this->record->items()->sum('line_total');

        $this->record->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $this->record->shipping_total,
        ]);
    }
}

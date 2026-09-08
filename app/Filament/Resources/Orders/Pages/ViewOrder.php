<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Pickup\PickupCodeService;
use App\Support\Shipping\ShipmentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // "Buat label dan resi dari admin cabang" (ROADMAP.md 7.1) —
            // books the real Biteship waybill for the courier already
            // quoted at checkout. See ShipmentService::bookForOrder().
            Action::make('ship')
                ->label('Buat Resi')
                ->icon(Heroicon::OutlinedTruck)
                ->visible(fn (Order $record) => $record->fulfilment === 'antar'
                    && $record->status === 'diproses'
                    && $record->shipment
                    && ! $record->shipment->is_booked)
                ->action(function (Order $record) {
                    try {
                        ShipmentService::make()->bookForOrder($record);
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title("Resi untuk pesanan #{$record->number} berhasil dibuat.")
                        ->send();
                }),

            // Issues the pickup code + QR and moves the order to "siap
            // diambil" (ROADMAP.md 7.2). See PickupCodeService::issue().
            Action::make('markReady')
                ->label('Tandai Siap Diambil')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->visible(fn (Order $record) => $record->fulfilment === 'ambil' && $record->status === 'diproses')
                ->action(function (Order $record) {
                    $record = PickupCodeService::issue($record);

                    Notification::make()
                        ->success()
                        ->title("Pesanan #{$record->number} siap diambil. Kode: {$record->pickup_code}.")
                        ->send();
                }),

            // Manual nudge for a late/lost Biteship webhook — same reasoning
            // as the DOKU payment side's "Cek Status" (ReconciliationResource).
            Action::make('checkShipmentStatus')
                ->label('Cek Status Kirim')
                ->icon(Heroicon::OutlinedArrowPath)
                ->visible(fn (Order $record) => (bool) $record->shipment?->is_booked)
                ->action(function (Order $record) {
                    try {
                        $result = ShipmentService::make()->reconcile($record->shipment);
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title("Status pengiriman #{$record->number} diperbarui: {$result->status}.")
                        ->send();
                }),
        ];
    }
}

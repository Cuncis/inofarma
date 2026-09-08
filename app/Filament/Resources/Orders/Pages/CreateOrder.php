<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\CodeSequence;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = CodeSequence::next(Order::withTrashed(), 'number', 'INO-', 0, 2450);
        $data['payment_status'] = $data['status'] === 'selesai' ? 'lunas' : 'belum bayar';

        return $data;
    }

    /**
     * The Repeater's own relationship save persists the line items after the
     * order is created — recompute the money columns from what actually
     * landed, same as OrderController::syncItems().
     */
    protected function afterCreate(): void
    {
        $subtotal = (int) $this->record->items()->sum('line_total');

        $this->record->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $this->record->shipping_total,
        ]);
    }
}

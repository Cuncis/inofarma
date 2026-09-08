<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Order;
use App\Support\AuditLogger;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Records that a refund happened (Fase 6) — this never calls
            // DOKU's refund API, see InvoiceController's docblock.
            Action::make('refund')
                ->label('Catat Refund')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->visible(fn (Order $record) => $record->payment_status === 'lunas'
                    && Auth::guard('web')->user()?->can('Pesanan:Refund'))
                ->schema([
                    Textarea::make('note')
                        ->label('Catatan')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data, Order $record) {
                    $record->latest_payment?->update([
                        'status' => 'refunded',
                        'refunded_at' => now(),
                        'refund_note' => $data['note'],
                    ]);

                    $record->update(['payment_status' => 'refund']);

                    AuditLogger::log('faktur.refund', $record, ['payment_status' => 'lunas'], [
                        'payment_status' => 'refund', 'note' => $data['note'],
                    ], branchId: $record->branch_id);

                    Notification::make()
                        ->success()
                        ->title("Refund untuk #{$record->number} dicatat.")
                        ->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    /** @var array<string, mixed> */
    private array $addressData = [];

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in CustomersTable.
            DeleteAction::make()
                ->before(function (Customer $record, DeleteAction $action) {
                    $count = $record->orders()->count();

                    if ($count > 0) {
                        Notification::make()
                            ->danger()
                            ->title("\"{$record->name}\" memiliki {$count} pesanan dan tidak bisa dihapus.")
                            ->body('Ubah statusnya menjadi Nonaktif.')
                            ->send();

                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * `city`/`address` aren't Customer columns — pull them from the default
     * address so the form starts filled, same data CustomerPresenter reads.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = $this->record->addresses->firstWhere('is_default', true)
            ?? $this->record->addresses->first();

        $data['city'] = $address?->kota;
        $data['address'] = $address?->address_line;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->addressData = [
            'kota' => $data['city'],
            'address_line' => $data['address'] ?? null,
        ];
        unset($data['city'], $data['address']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->addresses()->updateOrCreate(
            ['is_default' => true],
            [
                'label' => 'Rumah',
                'recipient_name' => $this->record->name,
                'phone' => $this->record->phone,
                'address_line' => ($this->addressData['address_line'] ?? null) ?: '—',
                'kota' => $this->addressData['kota'],
                'provinsi' => 'DKI Jakarta',
            ],
        );
    }
}

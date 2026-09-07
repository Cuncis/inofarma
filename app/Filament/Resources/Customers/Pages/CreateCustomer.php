<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\CodeSequence;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /** @var array<string, mixed> */
    private array $addressData = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->addressData = [
            'kota' => $data['city'],
            'address_line' => $data['address'] ?? null,
        ];
        unset($data['city'], $data['address']);

        $data['code'] = CodeSequence::next(Customer::withTrashed(), 'code', 'CUS-');
        // Staff never choose a customer's password. A random one is set so
        // the column is filled; the customer claims the account by resetting
        // it (Fase 3.3).
        $data['password'] = Hash::make(Str::random(40));
        $data['avatar_path'] = '/media/images/users/avatar-'.(Customer::count() % 12 + 1).'.jpg';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->addresses()->create([
            'is_default' => true,
            'label' => 'Rumah',
            'recipient_name' => $this->record->name,
            'phone' => $this->record->phone,
            'address_line' => $this->addressData['address_line'] ?: '—',
            'kota' => $this->addressData['kota'],
            'provinsi' => 'DKI Jakarta',
        ]);
    }
}

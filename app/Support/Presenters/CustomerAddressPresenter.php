<?php

namespace App\Support\Presenters;

use App\Models\CustomerAddress;
use Illuminate\Support\Collection;

class CustomerAddressPresenter
{
    /**
     * @param  iterable<CustomerAddress>  $addresses
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $addresses): array
    {
        return Collection::make($addresses)->map(fn (CustomerAddress $address) => self::toArray($address))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(CustomerAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipientName' => $address->recipient_name,
            'phone' => $address->phone,
            'addressLine' => $address->address_line,
            'kelurahan' => $address->kelurahan,
            'kecamatan' => $address->kecamatan,
            'kota' => $address->kota,
            'provinsi' => $address->provinsi,
            'postalCode' => $address->postal_code,
            'note' => $address->note,
            'latitude' => $address->latitude !== null ? (float) $address->latitude : null,
            'longitude' => $address->longitude !== null ? (float) $address->longitude : null,
            'isDefault' => $address->is_default,
            'fullAddress' => $address->full_address,
        ];
    }
}

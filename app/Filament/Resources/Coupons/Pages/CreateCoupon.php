<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Gratis Ongkir carries no product discount of its own — applied to
        // shipping_total by the checkout code instead, see Coupon::discountFor().
        if ($data['type'] === 'ongkir gratis') {
            $data['value'] = 0;
        }

        return $data;
    }
}

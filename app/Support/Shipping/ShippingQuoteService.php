<?php

namespace App\Support\Shipping;

use App\Models\Branch;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Support\Shipping\Biteship\BiteshipClient;

/**
 * Turns a branch, a delivery address and a cart's lines into the list of
 * courier options a customer can actually pick from at checkout — a live
 * `POST /v1/rates/couriers` call, origin = the branch's own coordinates
 * (never one central warehouse), so instant couriers (Gojek/Grab) quote
 * correctly and every branch's real distance is priced, not a network
 * average.
 */
class ShippingQuoteService
{
    public function __construct(private readonly BiteshipClient $client) {}

    public static function make(): self
    {
        return new self(BiteshipClient::fromConfig());
    }

    /**
     * @param  list<array{product: Product, quantity: int}>  $lines
     * @return list<array{courierCompany: string, courierType: string, courierName: string, serviceName: string, price: int, duration: ?string}>
     */
    public function quote(Branch $branch, CustomerAddress $address, array $lines): array
    {
        $pricing = $this->client->rates([
            'origin_latitude' => (float) $branch->latitude,
            'origin_longitude' => (float) $branch->longitude,
            'destination_latitude' => (float) $address->latitude,
            'destination_longitude' => (float) $address->longitude,
            'items' => collect($lines)->map(fn (array $line) => [
                'name' => $line['product']->name,
                'value' => $line['product']->price,
                'quantity' => $line['quantity'],
                'weight' => $line['product']->weight_grams,
                'height' => $line['product']->height_cm,
                'length' => $line['product']->length_cm,
                'width' => $line['product']->width_cm,
            ])->all(),
        ]);

        return collect($pricing)
            ->map(fn (array $option) => [
                'courierCompany' => $option['company'] ?? '',
                'courierType' => $option['type'] ?? '',
                'courierName' => $option['courier_name'] ?? ($option['company'] ?? ''),
                'serviceName' => $option['courier_service_name'] ?? '',
                'price' => (int) ($option['price'] ?? 0),
                'duration' => $option['duration'] ?? null,
            ])
            ->filter(fn (array $option) => $option['courierCompany'] !== '' && $option['courierType'] !== '')
            ->sortBy('price')
            ->values()
            ->all();
    }
}

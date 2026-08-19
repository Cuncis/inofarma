<?php

namespace App\Support\Shipping\Biteship;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper over Biteship's REST API — hand-rolled for the same reason as
 * `App\Support\Payments\Doku\DokuClient`: the unofficial Packagist packages
 * for this (`perigiweb/biteship`, `aliziodev/laravel-biteship`) are small,
 * lightly-maintained community wrappers around a handful of endpoints this
 * app can own directly without the supply-chain risk.
 *
 * Auth is a single `Authorization: <api key>` header — no OAuth, no HMAC,
 * unlike DOKU.
 */
class BiteshipClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    public static function fromConfig(): self
    {
        $apiKey = config('services.biteship.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Biteship belum dikonfigurasi. Isi BITESHIP_API_KEY di .env.');
        }

        return new self(config('services.biteship.base_url'), $apiKey);
    }

    /**
     * Every courier company active on this Biteship account — the `couriers`
     * whitelist `rates()` requires is built from this rather than a
     * hardcoded list, so a company activated later in the Biteship dashboard
     * (or deactivated) is picked up automatically, same principle as never
     * hardcoding DOKU's payment channels.
     *
     * @return list<string> courier company codes, e.g. ["jne", "jnt", "gojek"]
     */
    public function courierCodes(): array
    {
        $response = $this->request()->get($this->baseUrl.'/couriers');
        $body = $this->decode($response);

        return collect($body['couriers'] ?? [])->pluck('courier_code')->filter()->values()->all();
    }

    /**
     * Quote every available courier for a route and a set of items.
     *
     * @param  array<string, mixed>  $params  origin_latitude, origin_longitude,
     *                                        destination_latitude, destination_longitude, items
     * @return list<array<string, mixed>> the `pricing` array from Biteship
     */
    public function rates(array $params): array
    {
        $codes = $this->courierCodes();

        if ($codes === []) {
            return [];
        }

        $response = $this->request()->post($this->baseUrl.'/rates/couriers', [
            ...$params,
            'couriers' => implode(',', $codes),
        ]);

        return $this->decode($response)['pricing'] ?? [];
    }

    /**
     * Book an actual pickup and get back a waybill. `reference_id` should be
     * the order number — Biteship itself refuses a second order created with
     * the same reference, which is a second line of defence against a
     * double-booked shipment beyond the one `ShipmentService` keeps by
     * refusing to call this twice for a shipment that already has one.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createOrder(array $params): array
    {
        $response = $this->request()->post($this->baseUrl.'/orders', $params);

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function track(string $trackingId): array
    {
        $response = $this->request()->get($this->baseUrl.'/trackings/'.$trackingId);

        return $this->decode($response);
    }

    private function request(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => $this->apiKey])->acceptJson();
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Biteship menolak permintaan: '.($response->json('error') ?? $response->body()),
                previous: $exception,
            );
        }

        return $response->json() ?? [];
    }
}

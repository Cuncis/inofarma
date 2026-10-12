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
     * Biteship lists one entry per courier *service*, so a company appears many
     * times; the codes are de-duplicated.
     *
     * @return list<string> courier company codes, e.g. ["jne", "jnt", "gojek"]
     */
    public function courierCodes(): array
    {
        $response = $this->request()->get($this->baseUrl.'/couriers');
        $body = $this->decode($response);

        return collect($body['couriers'] ?? [])->pluck('courier_code')->filter()->unique()->values()->all();
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
            $reason = (string) ($response->json('error') ?? $response->body());

            throw new RuntimeException(self::explain($reason, $this->apiKey), previous: $exception);
        }

        return $response->json() ?? [];
    }

    /**
     * Biteship's own wording is terse English. The two refusals staff can fix
     * themselves in the Biteship dashboard get a plain-Indonesian next step;
     * anything else keeps Biteship's text so nothing is hidden.
     */
    public static function explain(string $reason, string $apiKey = ''): string
    {
        $lower = strtolower($reason);

        if (str_contains($lower, 'not been activated')) {
            $hint = str_starts_with($apiKey, 'biteship_test')
                ? 'Key yang dipakai adalah key TEST. Di dashboard Biteship nyalakan Testing Mode, buat key di sana (Pengaturan > API Key, berlabel Development), lalu pasang di .env.'
                : (str_starts_with($apiKey, 'biteship_live')
                    ? 'Key yang dipakai adalah key LIVE, yang belum diaktifkan Biteship untuk membuat resi. Ajukan aktivasi (Go Live) di dashboard Biteship, atau pakai key TEST di .env selama masih uji coba.'
                    : 'Jenis key tidak dikenali (harus diawali biteship_test atau biteship_live).');

            return 'Biteship menolak: API key belum diaktifkan untuk membuat resi. '.$hint.' Setelah mengganti key jalankan php artisan config:clear. Pesan asli: '.$reason;
        }

        if (str_contains($lower, 'balance') || str_contains($lower, 'saldo')) {
            return 'Biteship menolak: saldo Biteship tidak cukup untuk membuat resi. Isi saldo di dashboard Biteship, lalu coba lagi. Pesan asli: '.$reason;
        }

        return 'Biteship menolak permintaan: '.$reason;
    }
}

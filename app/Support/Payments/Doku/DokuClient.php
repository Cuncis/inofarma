<?php

namespace App\Support\Payments\Doku;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Thin wrapper over DOKU's REST API — deliberately hand-rolled rather than
 * one of the unofficial `composer.json` packages for this (several on
 * Packagist are unmaintained or explicitly marked abandoned): the surface
 * this app needs is two endpoints and one signature scheme, and owning that
 * directly means no supply-chain risk on an unmaintained dependency for
 * something this small.
 */
class DokuClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $secretKey,
    ) {}

    public static function fromConfig(): self
    {
        $clientId = config('services.doku.client_id');
        $secretKey = config('services.doku.secret_key');

        if (! $clientId || ! $secretKey) {
            throw new RuntimeException(
                'DOKU belum dikonfigurasi. Isi DOKU_CLIENT_ID dan DOKU_SECRET_KEY di .env.'
            );
        }

        return new self(config('services.doku.base_url'), $clientId, $secretKey);
    }

    /**
     * Create a DOKU Checkout payment session.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed> decoded response
     */
    public function createPayment(array $body, string $requestId): array
    {
        $path = '/checkout/v1/payment';
        $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $signature = DokuSignature::sign(
            $this->clientId, $requestId, $timestamp, $path,
            DokuSignature::digest($json), $this->secretKey,
        );

        $response = Http::withHeaders($this->headers($requestId, $timestamp, $signature))
            ->withBody($json, 'application/json')
            ->post($this->baseUrl.$path);

        return $this->decode($response);
    }

    /**
     * Query DOKU's own record of a transaction's status — used for
     * reconciliation and for an admin manually refreshing a stuck payment,
     * never as the primary source of truth (the webhook is).
     *
     * @return array<string, mixed>
     */
    public function checkStatus(string $invoiceNumber): array
    {
        $path = '/orders/v1/status/'.$invoiceNumber;
        $requestId = (string) Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $signature = DokuSignature::sign(
            $this->clientId, $requestId, $timestamp, $path, null, $this->secretKey,
        );

        $response = Http::withHeaders($this->headers($requestId, $timestamp, $signature))
            ->get($this->baseUrl.$path);

        return $this->decode($response);
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $requestId, string $timestamp, string $signature): array
    {
        return [
            'Client-Id' => $this->clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $signature,
        ];
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
                'DOKU menolak permintaan: '.($response->json('error_messages.0') ?? $response->body()),
                previous: $exception,
            );
        }

        return $response->json() ?? [];
    }
}

<?php

namespace App\Support\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin wrapper over the WhatsApp Cloud API (Meta's own official Business
 * API — see `services.whatsapp`) — hand-rolled for the same reason as the
 * DOKU and Biteship clients: a handful of endpoints, no supply-chain risk
 * from an unmaintained community package.
 *
 * Falls back to `Log::info()` when unconfigured, same convention as the
 * phone OTP in `Shop\AuthController` (Fase 3) — this app has never had real
 * SMS/WhatsApp credentials to send through, in any phase.
 *
 * Meta requires a pre-approved message *template* for any business-initiated
 * message (one the customer didn't just message first) — free-form text
 * only works inside a 24-hour window the customer opened, which an order
 * notification can't rely on. `sendTemplate()` is therefore the only send
 * method this client offers.
 */
class WhatsAppClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token,
        private readonly ?string $phoneNumberId,
    ) {}

    public static function make(): self
    {
        return new self(
            config('services.whatsapp.base_url'),
            config('services.whatsapp.token'),
            config('services.whatsapp.phone_number_id'),
        );
    }

    /**
     * @param  list<string>  $parameters  positional values for the template body's {{1}}, {{2}}, ...
     */
    public function sendTemplate(string $to, string $template, array $parameters, string $logFallback): void
    {
        if (! $this->token || ! $this->phoneNumberId) {
            Log::info("WhatsApp (belum dikonfigurasi) ke {$to}: {$logFallback}");

            return;
        }

        $body = [
            'messaging_product' => 'whatsapp',
            'to' => self::normalizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => 'id'],
                ...($parameters === [] ? [] : ['components' => [[
                    'type' => 'body',
                    'parameters' => collect($parameters)->map(fn (string $value) => ['type' => 'text', 'text' => $value])->all(),
                ]]]),
            ],
        ];

        try {
            $response = Http::withToken($this->token)->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $body);

            if ($response->failed()) {
                Log::warning('WhatsApp Cloud API menolak pesan.', [
                    'to' => $to, 'template' => $template, 'response' => $response->json(),
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /** Indonesian local format (0812...) to the international format the Cloud API expects (62812...), digits only. */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}

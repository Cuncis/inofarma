<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Support\Payments\Doku\DokuSignature;
use App\Support\Payments\DokuPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * DOKU's server-to-server notification — the *only* source of truth for
 * payment status (ROADMAP.md Fase 6: "bukan redirect browser"). The browser
 * redirect after checkout (`callback_url_result`, pointed at
 * `ui.pesanan.show`) is just where the customer ends up looking; it never
 * writes anything.
 *
 * Registered outside the `web` middleware group's CSRF check
 * (`bootstrap/app.php`) — DOKU can't carry a CSRF token — so signature
 * verification is the only thing standing between this endpoint and anyone
 * on the internet claiming a payment succeeded. It runs before anything else
 * here touches the database.
 */
class DokuWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();

        $verified = DokuSignature::verify(
            [
                'client-id' => (string) $request->header('Client-Id'),
                'request-id' => (string) $request->header('Request-Id'),
                'request-timestamp' => (string) $request->header('Request-Timestamp'),
                'signature' => (string) $request->header('Signature'),
            ],
            $rawBody,
            config('services.doku.notification_path'),
            (string) config('services.doku.client_id'),
            (string) config('services.doku.secret_key'),
        );

        if (! $verified) {
            Log::warning('Notifikasi DOKU ditolak: tanda tangan tidak valid.', [
                'request_id' => $request->header('Request-Id'),
            ]);

            return response()->json(['message' => 'invalid signature'], 401);
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return response()->json(['message' => 'invalid payload'], 400);
        }

        $payment = DokuPaymentService::applyNotification($payload);

        if (! $payment) {
            Log::info('Notifikasi DOKU untuk invoice yang tidak dikenal.', [
                'invoice_number' => $payload['order']['invoice_number'] ?? null,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}

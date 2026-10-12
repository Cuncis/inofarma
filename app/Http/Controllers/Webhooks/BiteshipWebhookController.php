<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Support\Shipping\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Biteship's server-to-server event notification (`order.status`,
 * `order.waybill_id`, `order.price`).
 *
 * Biteship's own docs, unlike DOKU's, describe no signature scheme for this
 * at all — the payload has no HMAC, no header to verify. Their guidance to
 * integrators is to "set up any necessary authentication for your webhook
 * endpoint" themselves, so `?token=` is that: a secret generated here and
 * pasted into the Notification URL configured in the Biteship dashboard.
 * It is the entire security boundary for this endpoint — see
 * `services.biteship.webhook_token`. The one exception is an empty request,
 * Biteship's check when the URL is installed in its dashboard.
 */
class BiteshipWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Biteship checks a new webhook URL by sending an empty request and wants
        // a plain 200 back, before any token could be in play. An empty body
        // carries no event and changes nothing, so it is answered without the
        // token; anything with a payload still has to present it.
        if ($request->all() === []) {
            return response()->json(['message' => 'ok']);
        }

        $expected = (string) config('services.biteship.webhook_token');

        if (! $expected || ! hash_equals($expected, (string) $request->query('token'))) {
            Log::warning('Notifikasi Biteship ditolak: token tidak valid.');

            return response()->json(['message' => 'invalid token'], 401);
        }

        $payload = $request->all();
        $shipment = ShipmentService::applyWebhookEvent($payload);

        if (! $shipment) {
            Log::info('Notifikasi Biteship untuk order_id yang tidak dikenal.', [
                'order_id' => $payload['order_id'] ?? null,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}

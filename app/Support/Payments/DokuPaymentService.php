<?php

namespace App\Support\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Support\OrderCancellation;
use App\Support\Payments\Doku\DokuClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrates one order's relationship with DOKU: opening a checkout
 * session (`createForOrder()`), and folding a webhook notification back into
 * `Payment`/`Order` state (`applyNotification()`). `DokuClient` only knows
 * how to talk to the two DOKU endpoints; this class knows what those calls
 * mean for this app's own models.
 */
class DokuPaymentService
{
    public function __construct(private readonly DokuClient $client) {}

    public static function make(): self
    {
        return new self(DokuClient::fromConfig());
    }

    /**
     * Open a new DOKU Checkout session for an order and record the attempt.
     * Deliberately runs outside any DB transaction — an external HTTP call
     * has no business holding row locks open, and if DOKU itself fails the
     * order the customer already has must not disappear with it.
     *
     * @throws RuntimeException on a DOKU-side failure — the order stands, so
     *                          the caller can offer to retry.
     */
    public function createForOrder(Order $order): Payment
    {
        $order->loadMissing('items', 'customer');

        $invoiceNumber = $this->nextInvoiceNumber($order);
        $requestId = (string) Str::uuid();
        $minutes = $order->expires_at
            ? max((int) now()->diffInMinutes($order->expires_at, false), 5)
            : 1440;

        $body = [
            'order' => [
                'amount' => $order->grand_total,
                'invoice_number' => $invoiceNumber,
                'currency' => 'IDR',
                'callback_url' => route('ui.track-order', $order->number),
                'callback_url_cancel' => route('ui.track-order', $order->number),
                'callback_url_result' => route('ui.track-order', $order->number),
                'line_items' => $order->items->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'sku' => $item->sku,
                ])->all(),
            ],
            'payment' => [
                'payment_due_date' => min($minutes, 999999),
            ],
            'customer' => [
                'id' => $order->customer->code,
                'name' => $order->recipient_name ?? $order->customer->name,
                'phone' => $order->recipient_phone ?? $order->customer->phone,
                'email' => $order->customer->email,
            ],
        ];

        $response = $this->client->createPayment($body, $requestId);
        $payment = $response['response']['payment'] ?? [];

        return $order->payments()->create([
            'gateway' => 'doku',
            'invoice_number' => $invoiceNumber,
            'status' => 'pending',
            'amount' => $order->grand_total,
            'request_id' => $requestId,
            'token_id' => $payment['token_id'] ?? null,
            'checkout_url' => $payment['url'] ?? null,
            'expires_at' => $order->expires_at,
            'raw_response' => $response,
        ]);
    }

    /**
     * Fold a verified webhook notification into `Payment`/`Order` state.
     *
     * Idempotent by design: if the payment this notification refers to is
     * already in the status being reported, this is a no-op past the point
     * of recording the raw payload — safe to call for the same notification
     * twice, which DOKU explicitly says it may send (ROADMAP.md Fase 6).
     *
     * @param  array<string, mixed>  $payload
     */
    public static function applyNotification(array $payload): ?Payment
    {
        $invoiceNumber = $payload['order']['invoice_number'] ?? null;
        $status = self::mapStatus($payload['transaction']['status'] ?? null);
        $channel = $payload['channel']['id'] ?? null;

        if (! $invoiceNumber || ! $status) {
            return null;
        }

        $payment = Payment::where('invoice_number', $invoiceNumber)->first();

        if (! $payment) {
            return null;
        }

        return DB::transaction(function () use ($payment, $status, $channel, $payload) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $alreadyApplied = $payment->status === $status;

            $payment->update([
                'status' => $status,
                'channel' => $channel ?? $payment->channel,
                'paid_at' => $status === 'success' ? ($payment->paid_at ?? now()) : $payment->paid_at,
                'refunded_at' => $status === 'refunded' ? ($payment->refunded_at ?? now()) : $payment->refunded_at,
                'raw_notification' => $payload,
            ]);

            if (! $alreadyApplied) {
                self::applyToOrder($payment->order, $status, $channel);
            }

            return $payment;
        });
    }

    private static function applyToOrder(Order $order, string $status, ?string $channel): void
    {
        match ($status) {
            'success' => $order->update([
                'payment_status' => 'lunas',
                'payment_method' => $channel ?? $order->payment_method,
                'paid_at' => $order->paid_at ?? now(),
                'status' => $order->status === 'menunggu pembayaran' ? 'diproses' : $order->status,
            ]),
            // A session DOKU itself expired before the customer paid — same
            // treatment as our own scheduled sweep (`pesanan:kadaluwarsakan`),
            // just triggered by DOKU instead of arriving late.
            'expired' => $order->status === 'menunggu pembayaran'
                ? OrderCancellation::apply($order, 'kedaluwarsa')
                : null,
            // Refund is recorded, not auto-reversed — see `.ai/rules` and
            // `Admin\PaymentController::refund()` for why stock isn't
            // automatically returned here.
            'refunded' => $order->update(['payment_status' => 'refund']),
            default => null,
        };
    }

    /** DOKU's vocabulary → this app's `payments.status` enum. */
    private static function mapStatus(?string $dokuStatus): ?string
    {
        return match ($dokuStatus) {
            'SUCCESS' => 'success',
            'FAILED', 'TIMEOUT' => 'failed',
            'EXPIRED' => 'expired',
            'REFUNDED' => 'refunded',
            'PENDING', 'REDIRECT' => 'pending',
            default => null,
        };
    }

    private function nextInvoiceNumber(Order $order): string
    {
        $attempt = $order->payments()->count() + 1;

        return $attempt === 1 ? $order->number : "{$order->number}-R{$attempt}";
    }
}

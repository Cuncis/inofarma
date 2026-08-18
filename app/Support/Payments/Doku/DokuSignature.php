<?php

namespace App\Support\Payments\Doku;

/**
 * DOKU's "non-SNAP" HMAC signature scheme, used by DOKU Checkout, the Check
 * Status API, and HTTP notifications alike.
 *
 * To sign a request: build one component per line, `Label:value`, in this
 * exact order, joined by `\n` with no trailing newline —
 *
 *   Client-Id:{client id}
 *   Request-Id:{a fresh UUID per request}
 *   Request-Timestamp:{ISO 8601, UTC}
 *   Request-Target:{the URL path being called, e.g. /checkout/v1/payment}
 *   Digest:{base64(sha256(raw request body))}     — omitted entirely for GET
 *
 * then HMAC-SHA256 that string with the merchant's Secret Key, base64-encode
 * the result, and prefix it `HMACSHA256=`.
 *
 * Verifying an incoming notification is the same algorithm run in reverse:
 * recompute the expected signature from the request DOKU actually sent (its
 * headers plus the raw body we received) using our own secret key, and
 * compare in constant time. Never trust a notification whose signature
 * doesn't match — see `DokuWebhookController`.
 */
class DokuSignature
{
    public static function digest(string $rawBody): string
    {
        return base64_encode(hash('sha256', $rawBody, true));
    }

    public static function sign(
        string $clientId,
        string $requestId,
        string $timestamp,
        string $requestTarget,
        ?string $digest,
        string $secretKey,
    ): string {
        $lines = [
            "Client-Id:{$clientId}",
            "Request-Id:{$requestId}",
            "Request-Timestamp:{$timestamp}",
            "Request-Target:{$requestTarget}",
        ];

        if ($digest !== null) {
            $lines[] = "Digest:{$digest}";
        }

        $stringToSign = implode("\n", $lines);
        $hmac = base64_encode(hash_hmac('sha256', $stringToSign, $secretKey, true));

        return "HMACSHA256={$hmac}";
    }

    /**
     * @param  array<string, string>  $headers  case-insensitive keys: client-id, request-id, request-timestamp, signature
     */
    public static function verify(
        array $headers,
        string $rawBody,
        string $requestTarget,
        string $expectedClientId,
        string $secretKey,
    ): bool {
        $headers = array_change_key_case($headers, CASE_LOWER);

        $clientId = $headers['client-id'] ?? null;
        $requestId = $headers['request-id'] ?? null;
        $timestamp = $headers['request-timestamp'] ?? null;
        $signature = $headers['signature'] ?? null;

        if (! $clientId || ! $requestId || ! $timestamp || ! $signature) {
            return false;
        }

        // A notification claiming to be from a different merchant is bogus
        // regardless of what its signature says.
        if (! hash_equals($expectedClientId, $clientId)) {
            return false;
        }

        $expected = self::sign($clientId, $requestId, $timestamp, $requestTarget, self::digest($rawBody), $secretKey);

        return hash_equals($expected, $signature);
    }
}

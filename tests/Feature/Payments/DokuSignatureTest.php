<?php

namespace Tests\Feature\Payments;

use App\Support\Payments\Doku\DokuSignature;
use Tests\TestCase;

class DokuSignatureTest extends TestCase
{
    public function test_digest_is_base64_of_sha256_of_the_body(): void
    {
        $body = '{"order":{"amount":20000}}';

        $expected = base64_encode(hash('sha256', $body, true));

        $this->assertSame($expected, DokuSignature::digest($body));
    }

    public function test_sign_and_verify_round_trip(): void
    {
        $signature = DokuSignature::sign(
            'MCH-0001', 'req-1', '2026-08-19T00:00:00Z', '/checkout/v1/payment',
            DokuSignature::digest('{"a":1}'), 'super-secret',
        );

        $this->assertStringStartsWith('HMACSHA256=', $signature);

        $verified = DokuSignature::verify(
            [
                'Client-Id' => 'MCH-0001',
                'Request-Id' => 'req-1',
                'Request-Timestamp' => '2026-08-19T00:00:00Z',
                'Signature' => $signature,
            ],
            '{"a":1}',
            '/checkout/v1/payment',
            'MCH-0001',
            'super-secret',
        );

        $this->assertTrue($verified);
    }

    public function test_verify_rejects_a_tampered_body(): void
    {
        $signature = DokuSignature::sign(
            'MCH-0001', 'req-1', '2026-08-19T00:00:00Z', '/doku/notifikasi',
            DokuSignature::digest('{"order":{"invoice_number":"INO-1"}}'), 'super-secret',
        );

        $verified = DokuSignature::verify(
            [
                'Client-Id' => 'MCH-0001',
                'Request-Id' => 'req-1',
                'Request-Timestamp' => '2026-08-19T00:00:00Z',
                'Signature' => $signature,
            ],
            // A forged notification claiming a different invoice was paid.
            '{"order":{"invoice_number":"INO-2"}}',
            '/doku/notifikasi',
            'MCH-0001',
            'super-secret',
        );

        $this->assertFalse($verified);
    }

    public function test_verify_rejects_the_wrong_secret_key(): void
    {
        $signature = DokuSignature::sign(
            'MCH-0001', 'req-1', '2026-08-19T00:00:00Z', '/doku/notifikasi',
            DokuSignature::digest('{}'), 'the-real-secret',
        );

        $verified = DokuSignature::verify(
            ['Client-Id' => 'MCH-0001', 'Request-Id' => 'req-1', 'Request-Timestamp' => '2026-08-19T00:00:00Z', 'Signature' => $signature],
            '{}',
            '/doku/notifikasi',
            'MCH-0001',
            'a-guessed-secret',
        );

        $this->assertFalse($verified);
    }

    public function test_verify_rejects_a_client_id_that_does_not_match(): void
    {
        $signature = DokuSignature::sign(
            'SOMEONE-ELSE', 'req-1', '2026-08-19T00:00:00Z', '/doku/notifikasi',
            DokuSignature::digest('{}'), 'super-secret',
        );

        $verified = DokuSignature::verify(
            ['Client-Id' => 'SOMEONE-ELSE', 'Request-Id' => 'req-1', 'Request-Timestamp' => '2026-08-19T00:00:00Z', 'Signature' => $signature],
            '{}',
            '/doku/notifikasi',
            'MCH-0001',
            'super-secret',
        );

        $this->assertFalse($verified);
    }

    public function test_verify_rejects_missing_headers(): void
    {
        $verified = DokuSignature::verify(
            ['Client-Id' => 'MCH-0001'],
            '{}',
            '/doku/notifikasi',
            'MCH-0001',
            'super-secret',
        );

        $this->assertFalse($verified);
    }

    public function test_a_get_signature_omits_the_digest_line(): void
    {
        $withDigest = DokuSignature::sign('MCH-0001', 'req-1', 'ts', '/orders/v1/status/INV-1', 'somedigest', 'secret');
        $withoutDigest = DokuSignature::sign('MCH-0001', 'req-1', 'ts', '/orders/v1/status/INV-1', null, 'secret');

        $this->assertNotSame($withDigest, $withoutDigest);
    }
}

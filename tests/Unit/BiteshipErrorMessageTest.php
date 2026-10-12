<?php

namespace Tests\Unit;

use App\Support\Shipping\Biteship\BiteshipClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class BiteshipErrorMessageTest extends TestCase
{
    public function test_an_inactive_key_explains_what_to_do_in_the_biteship_dashboard(): void
    {
        $message = BiteshipClient::explain('Key has not been activated');

        $this->assertStringContainsString('belum diaktifkan', $message);
        $this->assertStringContainsString('Key has not been activated', $message);
    }

    public function test_the_advice_depends_on_whether_the_key_is_test_or_live(): void
    {
        $this->assertStringContainsString('key TEST', BiteshipClient::explain('Key has not been activated', 'biteship_test.abc'));
        $this->assertStringContainsString('Testing Mode', BiteshipClient::explain('Key has not been activated', 'biteship_test.abc'));
        $this->assertStringContainsString('key LIVE', BiteshipClient::explain('Key has not been activated', 'biteship_live.abc'));
        $this->assertStringContainsString('Jenis key tidak dikenali', BiteshipClient::explain('Key has not been activated', 'xyz'));
    }

    public function test_an_empty_balance_is_called_out(): void
    {
        $this->assertStringContainsString('saldo', BiteshipClient::explain('Insufficient balance'));
    }

    #[DataProvider('otherReasons')]
    public function test_any_other_reason_keeps_biteships_own_text(string $reason): void
    {
        $this->assertSame('Biteship menolak permintaan: '.$reason, BiteshipClient::explain($reason));
    }

    /** @return array<string, array{string}> */
    public static function otherReasons(): array
    {
        return ['postal code' => ['Failed due to invalid or missing postal code'], 'empty' => ['']];
    }

    public function test_creating_an_order_with_an_inactive_key_throws_the_clear_message(): void
    {
        Http::fake(['api.biteship.com/v1/orders' => Http::response(['success' => false, 'error' => 'Key has not been activated'], 403)]);

        try {
            (new BiteshipClient('https://api.biteship.com/v1', 'k'))->createOrder([]);
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('belum diaktifkan', $exception->getMessage());
        }
    }
}

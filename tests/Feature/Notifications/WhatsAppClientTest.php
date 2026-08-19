<?php

namespace Tests\Feature\Notifications;

use App\Support\Notifications\WhatsAppClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * `WhatsAppClient` — phone normalization, the log fallback when unconfigured
 * (this app has never had real WhatsApp/SMS credentials, any phase), and
 * the real Cloud API request shape when it is.
 */
class WhatsAppClientTest extends TestCase
{
    public function test_local_phone_numbers_are_normalized_to_international_format(): void
    {
        $this->assertSame('628123456789', WhatsAppClient::normalizePhone('0812-3456-789'));
        $this->assertSame('628123456789', WhatsAppClient::normalizePhone('+62 812 3456 789'));
    }

    public function test_an_unconfigured_client_never_calls_the_real_api(): void
    {
        config(['services.whatsapp.token' => null, 'services.whatsapp.phone_number_id' => null]);
        Http::fake();

        WhatsAppClient::make()->sendTemplate('0812', 'pesanan_dikirim', ['x'], 'fallback text');

        Http::assertNothingSent();
    }

    public function test_a_configured_client_sends_a_real_template_request(): void
    {
        config(['services.whatsapp.token' => 'token-x', 'services.whatsapp.phone_number_id' => '1234']);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200)]);

        WhatsAppClient::make()->sendTemplate('0812-000-111', 'pesanan_dikirim', ['Budi', 'INO-1'], 'fallback');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/1234/messages')
            && $request['to'] === '62812000111'
            && $request['type'] === 'template'
            && $request['template']['name'] === 'pesanan_dikirim'
            && $request['template']['components'][0]['parameters'][0]['text'] === 'Budi');
    }
}

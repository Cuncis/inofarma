<?php

namespace Tests\Feature\Console;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckBiteshipTest extends TestCase
{
    use RefreshDatabase;

    private function fakeBiteship(array $ratesResponse): void
    {
        Http::fake([
            'api.biteship.com/v1/couriers' => Http::response(['couriers' => [['courier_code' => 'jne'], ['courier_code' => 'jne']]], 200),
            'api.biteship.com/v1/rates/couriers' => Http::response(...$ratesResponse),
        ]);
    }

    public function test_it_fails_when_the_api_key_is_missing(): void
    {
        config(['services.biteship.api_key' => null]);

        $this->artisan('biteship:cek')->expectsOutputToContain('BITESHIP_API_KEY kosong')->assertFailed();
    }

    public function test_a_delivering_branch_without_coordinates_is_called_out_and_no_price_is_requested(): void
    {
        config(['services.biteship.api_key' => 'biteship_live.test']);
        $this->fakeBiteship([['pricing' => []], 200]);
        Branch::factory()->create(['name' => 'Cabang Tanpa Titik', 'supports_delivery' => true, 'latitude' => null, 'longitude' => null]);

        $this->artisan('biteship:cek')
            ->expectsOutputToContain('koordinat (lintang/bujur) belum diisi')
            ->assertFailed();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/rates/couriers'));
    }

    public function test_a_complete_branch_that_gets_prices_passes(): void
    {
        config(['services.biteship.api_key' => 'biteship_live.test']);
        $this->fakeBiteship([['pricing' => [['company' => 'jne', 'type' => 'reg', 'price' => 12000]]], 200]);
        Branch::factory()->create(['supports_delivery' => true, 'latitude' => -6.2, 'longitude' => 106.8, 'address_line' => 'Jl. Nyata No. 1', 'phone' => '021123']);

        $this->artisan('biteship:cek')->expectsOutputToContain('Ongkir berhasil')->assertSuccessful();
    }

    public function test_biteships_own_rejection_is_shown(): void
    {
        config(['services.biteship.api_key' => 'biteship_live.test']);
        $this->fakeBiteship([['success' => false, 'error' => 'Failed due to invalid or missing postal code.'], 400]);
        Branch::factory()->create(['supports_delivery' => true, 'latitude' => -6.2, 'longitude' => 106.8, 'address_line' => 'Jl. Nyata No. 1', 'phone' => '021123']);

        $this->artisan('biteship:cek')->expectsOutputToContain('invalid or missing postal code')->assertFailed();
    }
}

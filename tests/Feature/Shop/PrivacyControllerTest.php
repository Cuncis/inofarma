<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * "Privasi Saya" — PDP (UU 27/2022) self-service (ROADMAP.md Fase 9.2):
 * download my data, delete my account. Both act only on the signed-in
 * customer's own record.
 */
class PrivacyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_download_their_own_data(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif', 'name' => 'Budi Santoso']);
        CustomerAddress::factory()->for($customer)->create(['address_line' => 'Jl. Melati No. 3']);
        Order::factory()->create(['customer_id' => $customer->id, 'number' => 'INO-TEST-1']);

        $response = $this->actingAs($customer, 'customer')->get('/ui/privasi-saya/unduh');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $payload = json_decode($response->getContent(), true);

        $this->assertSame('Budi Santoso', $payload['profil']['nama']);
        $this->assertSame('Jl. Melati No. 3', $payload['alamat'][0]['alamat']);
        $this->assertSame('INO-TEST-1', $payload['pesanan'][0]['nomor']);
    }

    public function test_a_customer_can_delete_their_own_account_with_the_right_password(): void
    {
        $customer = Customer::factory()->create([
            'status' => 'aktif', 'email' => 'hapus@mail.com', 'password' => Hash::make('password123'),
        ]);
        CustomerAddress::factory()->for($customer)->create();

        $this->actingAs($customer, 'customer')
            ->delete('/ui/privasi-saya', ['password' => 'password123'])
            ->assertRedirect(route('home'));

        $this->assertFalse(Auth::guard('customer')->check());
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertSame(0, CustomerAddress::where('customer_id', $customer->id)->count());

        $fresh = Customer::withTrashed()->find($customer->id);
        $this->assertNotSame('hapus@mail.com', $fresh->email);
        $this->assertNull($fresh->phone);
    }

    public function test_deleting_an_account_with_the_wrong_password_is_refused(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif', 'password' => Hash::make('password123')]);

        $this->actingAs($customer, 'customer')
            ->delete('/ui/privasi-saya', ['password' => 'salah'])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }

    public function test_an_orders_history_survives_the_customers_own_account_deletion(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif', 'password' => Hash::make('password123')]);
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer, 'customer')
            ->delete('/ui/privasi-saya', ['password' => 'password123']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'customer_id' => $customer->id]);
    }
}

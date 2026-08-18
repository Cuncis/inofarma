<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Lanjutkan Pembayaran" — reopening a DOKU session for an order that
 * already exists, either because the first session expired or DOKU was
 * unreachable when the order was placed.
 */
class PaymentRetryTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDoku(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'test-secret']);

        Http::fake([
            'api-sandbox.doku.com/*' => Http::response([
                'message' => ['SUCCESS'],
                'response' => [
                    'order' => ['amount' => '0', 'invoice_number' => 'x', 'session_id' => 'sess'],
                    'payment' => [
                        'token_id' => 'tok_retry',
                        'url' => 'https://sandbox.doku.com/checkout-link-v2/tok_retry',
                        'expired_date' => '20260101000000',
                    ],
                ],
            ], 200),
        ]);
    }

    private function makeUnpaidOrder(Customer $customer): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        return Order::factory()->create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'fulfilment' => 'ambil', 'status' => 'menunggu pembayaran',
            'payment_status' => 'belum bayar', 'payment_method' => 'online',
            'grand_total' => 30000, 'number' => 'INO-'.$branch->code.'-RETRY',
            'expires_at' => now()->addDay(),
        ]);
    }

    public function test_a_customer_can_reopen_a_payment_session_for_their_own_order(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->fakeDoku();

        $this->actingAs($customer, 'customer');

        $this->post("/ui/pesanan/{$order->number}/bayar")
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/tok_retry');

        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_a_second_attempt_gets_its_own_invoice_number(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->fakeDoku();

        $this->actingAs($customer, 'customer');
        $this->post("/ui/pesanan/{$order->number}/bayar");
        $this->post("/ui/pesanan/{$order->number}/bayar");

        $invoiceNumbers = Payment::where('order_id', $order->id)->pluck('invoice_number');
        $this->assertCount(2, $invoiceNumbers->unique());
        $this->assertTrue($invoiceNumbers->contains($order->number));
        $this->assertTrue($invoiceNumbers->contains("{$order->number}-R2"));
    }

    public function test_a_paid_order_cannot_reopen_a_payment_session(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $order->update(['payment_status' => 'lunas']);

        $this->actingAs($customer, 'customer');
        $this->post("/ui/pesanan/{$order->number}/bayar")->assertSessionHas('error');

        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_a_customer_cannot_reopen_payment_for_another_customers_order(): void
    {
        $owner = Customer::factory()->create(['status' => 'aktif']);
        $intruder = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($owner);

        $this->actingAs($intruder, 'customer');
        $this->post("/ui/pesanan/{$order->number}/bayar")->assertNotFound();
    }
}

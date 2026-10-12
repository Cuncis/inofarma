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
use Illuminate\Http\Client\Factory;
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

    public function test_the_doku_page_is_limited_to_virtual_account_qris_and_e_wallets(): void
    {
        $this->fakeDoku();
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);

        $this->actingAs($customer, 'customer')->post("/pesanan/{$order->number}/bayar");

        Http::assertSent(function ($request) {
            $methods = $request->data()['payment']['payment_method_types'] ?? [];

            return str_contains($request->url(), '/checkout/v1/payment')
                && in_array('QRIS', $methods, true)
                && in_array('VIRTUAL_ACCOUNT_BCA', $methods, true)
                && in_array('EMONEY_OVO', $methods, true)
                && ! in_array('CREDIT_CARD', $methods, true);
        });
    }

    public function test_the_line_items_always_add_up_to_the_amount_doku_is_asked_to_charge(): void
    {
        $this->fakeDoku();
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $order->items()->create([
            'product_id' => Product::first()->id, 'product_name' => 'Obat', 'sku' => 'SKU-1',
            'unit_price' => 20000, 'quantity' => 2, 'line_total' => 40000, 'batches_consumed' => [],
        ]);
        $order->update(['subtotal' => 40000, 'shipping_total' => 10000, 'discount_total' => 0, 'grand_total' => 50000]);

        $this->actingAs($customer, 'customer')->post("/pesanan/{$order->number}/bayar");

        Http::assertSent(function ($request) {
            $order = $request->data()['order'] ?? null;

            return $order
                && $order['amount'] === 50000
                && collect($order['line_items'])->sum(fn ($line) => $line['price'] * $line['quantity']) === 50000
                && collect($order['line_items'])->contains(fn ($line) => $line['name'] === 'Ongkos kirim');
        });

        $order->update(['discount_total' => 5000, 'grand_total' => 45000]);
        Http::fake();
        $this->fakeDoku();
        $this->actingAs($customer, 'customer')->post("/pesanan/{$order->number}/bayar");

        Http::assertSent(fn ($request) => ($request->data()['order']['amount'] ?? null) === 45000
            && count($request->data()['order']['line_items']) === 1
            && $request->data()['order']['line_items'][0]['price'] === 45000);
    }

    public function test_returning_from_doku_marks_the_order_paid_even_if_the_webhook_is_late(): void
    {
        $this->fakeDoku();
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->actingAs($customer, 'customer')->post("/pesanan/{$order->number}/bayar");

        Http::swap(new Factory);
        Http::fake(['api-sandbox.doku.com/orders/v1/status/*' => Http::response([
            'order' => ['invoice_number' => $order->number],
            'transaction' => ['status' => 'SUCCESS'],
            'channel' => ['id' => 'QRIS'],
        ], 200)]);

        $this->get("/pesanan/{$order->number}")->assertOk();

        $this->assertSame('lunas', $order->fresh()->payment_status);
        $this->assertSame('pembayaran diterima', $order->fresh()->status);
    }

    public function test_the_order_page_still_opens_when_doku_cannot_be_reached(): void
    {
        $this->fakeDoku();
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->actingAs($customer, 'customer')->post("/pesanan/{$order->number}/bayar");

        Http::swap(new Factory);
        Http::fake(['api-sandbox.doku.com/orders/v1/status/*' => Http::response(['message' => ['ERR']], 500)]);

        $this->get("/pesanan/{$order->number}")->assertOk();
        $this->assertSame('belum bayar', $order->fresh()->payment_status);
    }

    public function test_a_customer_can_reopen_a_payment_session_for_their_own_order(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->fakeDoku();

        $this->actingAs($customer, 'customer');

        $this->post("/pesanan/{$order->number}/bayar")
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/tok_retry');

        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    /**
     * `Inertia::location()` returns a plain `Symfony\...\Response` (409 +
     * `X-Inertia-Location`) for a real Inertia XHR, not the `RedirectResponse`
     * a bare `$this->post()` gets back — a controller declaring its return
     * type as `RedirectResponse` throws a `TypeError` the moment a real
     * browser (which always sends `X-Inertia: true`) hits this. The plain
     * request in the test above never exercises that branch, so this one
     * pins it down explicitly.
     */
    public function test_reopening_a_payment_session_works_for_a_real_inertia_request(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->fakeDoku();

        $this->actingAs($customer, 'customer');

        $this->withHeaders(['X-Inertia' => 'true'])
            ->post("/pesanan/{$order->number}/bayar")
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://sandbox.doku.com/checkout-link-v2/tok_retry');
    }

    public function test_a_second_attempt_gets_its_own_invoice_number(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($customer);
        $this->fakeDoku();

        $this->actingAs($customer, 'customer');
        $this->post("/pesanan/{$order->number}/bayar");
        $this->post("/pesanan/{$order->number}/bayar");

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
        $this->post("/pesanan/{$order->number}/bayar")->assertSessionHas('error');

        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_a_customer_cannot_reopen_payment_for_another_customers_order(): void
    {
        $owner = Customer::factory()->create(['status' => 'aktif']);
        $intruder = Customer::factory()->create(['status' => 'aktif']);
        $order = $this->makeUnpaidOrder($owner);

        $this->actingAs($intruder, 'customer');
        $this->post("/pesanan/{$order->number}/bayar")->assertNotFound();
    }
}

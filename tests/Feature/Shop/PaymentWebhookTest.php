<?php

namespace Tests\Feature\Shop;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\Inventory\StockAllocator;
use App\Support\Payments\Doku\DokuSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * DOKU's notification is the only source of truth for payment status
 * (ROADMAP.md Fase 6) — these tests exercise `POST /doku/notifikasi`
 * directly, as DOKU itself would call it, signature and all.
 */
class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'MCH-TEST';

    private const SECRET = 'test-secret-key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.doku.client_id' => self::CLIENT_ID,
            'services.doku.secret_key' => self::SECRET,
            'services.doku.notification_path' => '/doku/notifikasi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function notify(array $payload): TestResponse
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $requestId = 'notif-'.uniqid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $signature = DokuSignature::sign(
            self::CLIENT_ID, $requestId, $timestamp, '/doku/notifikasi',
            DokuSignature::digest($body), self::SECRET,
        );

        return $this->call('POST', '/doku/notifikasi', server: [
            'HTTP_Client-Id' => self::CLIENT_ID,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $timestamp,
            'HTTP_Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], content: $body);
    }

    private function makeOrderWithPayment(string $paymentStatus = 'pending'): Payment
    {
        $branch = Branch::factory()->create(['supports_pickup' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $stock = BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10, 'reserved_quantity' => 0]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'fulfilment' => 'ambil',
            'status' => 'menunggu pembayaran',
            'payment_status' => 'belum bayar',
            'payment_method' => 'online',
            'grand_total' => 50000,
            'number' => 'INO-'.$branch->code.'-TEST',
            'expires_at' => now()->addDay(),
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);

        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 50000, 'quantity' => 1, 'line_total' => 50000, 'batches_consumed' => $manifest,
        ]);

        return Payment::factory()->for($order)->create([
            'invoice_number' => $order->number, 'amount' => 50000, 'status' => $paymentStatus,
        ]);
    }

    public function test_a_notification_with_an_invalid_signature_is_rejected(): void
    {
        $payment = $this->makeOrderWithPayment();

        $response = $this->postJson('/doku/notifikasi', [
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ], ['Client-Id' => self::CLIENT_ID, 'Request-Id' => 'x', 'Request-Timestamp' => 'x', 'Signature' => 'HMACSHA256=bogus']);

        $response->assertStatus(401);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('belum bayar', $payment->order->fresh()->payment_status);
    }

    public function test_a_verified_success_notification_marks_the_order_paid_and_advances_its_status(): void
    {
        $payment = $this->makeOrderWithPayment();

        $this->notify([
            'channel' => ['id' => 'VIRTUAL_ACCOUNT_BCA'],
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ])->assertOk();

        $order = $payment->order->fresh();
        $this->assertSame('lunas', $order->payment_status);
        $this->assertSame('diproses', $order->status);
        $this->assertSame('VIRTUAL_ACCOUNT_BCA', $order->payment_method);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('success', $payment->fresh()->status);
    }

    public function test_a_duplicate_success_notification_does_not_double_apply(): void
    {
        $payment = $this->makeOrderWithPayment();

        $this->notify([
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ])->assertOk();

        $paidAt = $payment->order->fresh()->paid_at;

        // DOKU may resend the same notification (ROADMAP.md Fase 6).
        $this->notify([
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ])->assertOk();

        $order = $payment->order->fresh();
        $this->assertSame('lunas', $order->payment_status);
        $this->assertTrue($paidAt->equalTo($order->paid_at));
    }

    public function test_an_expired_notification_returns_stock_and_marks_the_order_kedaluwarsa(): void
    {
        $payment = $this->makeOrderWithPayment();
        $product = $payment->order->items->first()->product;
        $branch = $payment->order->branch;

        $this->assertSame(9, $product->stockAt($branch)->fresh()->quantity);

        $this->notify([
            'transaction' => ['status' => 'EXPIRED'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ])->assertOk();

        $order = $payment->order->fresh();
        $this->assertSame('kedaluwarsa', $order->status);
        $this->assertSame(10, $product->stockAt($branch)->fresh()->quantity);
        $this->assertSame('expired', $payment->fresh()->status);
    }

    public function test_a_refunded_notification_marks_the_order_refund_without_touching_stock(): void
    {
        $payment = $this->makeOrderWithPayment('success');
        $payment->order->update(['payment_status' => 'lunas', 'status' => 'selesai', 'paid_at' => now()]);
        $product = $payment->order->items->first()->product;
        $branch = $payment->order->branch;
        $before = $product->stockAt($branch)->fresh()->quantity;

        $this->notify([
            'transaction' => ['status' => 'REFUNDED'],
            'order' => ['invoice_number' => $payment->invoice_number, 'amount' => 50000],
        ])->assertOk();

        $order = $payment->order->fresh();
        $this->assertSame('refund', $order->payment_status);
        $this->assertSame('selesai', $order->status);
        $this->assertSame($before, $product->stockAt($branch)->fresh()->quantity);
    }

    public function test_a_notification_for_an_unknown_invoice_is_acknowledged_without_error(): void
    {
        config(['services.doku.client_id' => self::CLIENT_ID, 'services.doku.secret_key' => self::SECRET]);

        $this->notify([
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => 'INO-DOES-NOT-EXIST', 'amount' => 1000],
        ])->assertOk();
    }
}

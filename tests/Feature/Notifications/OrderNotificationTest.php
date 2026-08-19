<?php

namespace Tests\Feature\Notifications;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\Admin\NewOrderAtBranch;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCompleted;
use App\Notifications\OrderConfirmed;
use App\Notifications\OrderReadyForPickup;
use App\Notifications\OrderShipped;
use App\Notifications\PaymentReceived;
use App\Support\Inventory\StockAllocator;
use App\Support\OrderCancellation;
use App\Support\Payments\DokuPaymentService;
use App\Support\Pickup\PickupCodeService;
use App\Support\Shipping\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * ROADMAP.md Fase 8's "selesai bila": every order status/payment change
 * fires the right notification. `App\Observers\OrderObserver` is the single
 * place all of these come from — these tests drive it through the real
 * Fase 5-7 flows that trigger each transition, not by calling the observer
 * directly.
 */
class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $branch = Branch::factory()->create(['supports_pickup' => true, 'supports_delivery' => true]);
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create(array_merge([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
        ], $overrides));

        $manifest = (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'unit_price' => 10000, 'quantity' => 1, 'line_total' => 10000, 'batches_consumed' => $manifest,
        ]);

        return $order->fresh();
    }

    public function test_creating_an_order_notifies_the_customer_and_the_branchs_staff(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $inactiveStaff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => false]);
        $customer = Customer::factory()->create();

        $order = Order::factory()->create(['branch_id' => $branch->id, 'customer_id' => $customer->id]);

        Notification::assertSentTo($customer, OrderConfirmed::class);
        Notification::assertSentTo($staff, NewOrderAtBranch::class);
        Notification::assertNotSentTo($inactiveStaff, NewOrderAtBranch::class);
    }

    public function test_a_successful_doku_payment_notifies_the_customer(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar']);
        $order->payments()->create([
            'gateway' => 'doku', 'invoice_number' => $order->number, 'status' => 'pending', 'amount' => $order->grand_total,
            'request_id' => 'req-1',
        ]);

        DokuPaymentService::applyNotification([
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $order->number, 'amount' => $order->grand_total],
        ]);

        Notification::assertSentTo($order->fresh()->customer, PaymentReceived::class);
    }

    public function test_paying_twice_only_notifies_once(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar']);
        $order->payments()->create([
            'gateway' => 'doku', 'invoice_number' => $order->number, 'status' => 'pending', 'amount' => $order->grand_total,
            'request_id' => 'req-1',
        ]);

        $payload = [
            'transaction' => ['status' => 'SUCCESS'],
            'order' => ['invoice_number' => $order->number, 'amount' => $order->grand_total],
        ];
        DokuPaymentService::applyNotification($payload);
        DokuPaymentService::applyNotification($payload);

        Notification::assertSentToTimes($order->fresh()->customer, PaymentReceived::class, 1);
    }

    public function test_booking_a_waybill_notifies_the_customer_over_mail_and_whatsapp(): void
    {
        Notification::fake();
        config(['services.biteship.api_key' => 'test-key']);
        Http::fake(['api.biteship.com/v1/orders' => Http::response([
            'id' => 'biteship-1', 'status' => 'confirmed',
            'courier' => ['tracking_id' => 'BST-1', 'waybill_id' => 'JNE001'],
        ], 200)]);

        $order = $this->makeOrder([
            'fulfilment' => 'antar', 'status' => 'diproses',
            'shipping_address' => 'Jl. Contoh', 'shipping_latitude' => -6.2, 'shipping_longitude' => 106.8,
        ]);
        Shipment::factory()->for($order)->create();

        ShipmentService::make()->bookForOrder($order->fresh());

        Notification::assertSentTo($order->fresh()->customer, OrderShipped::class);
    }

    public function test_marking_an_order_ready_notifies_the_customer_with_the_pickup_code(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['fulfilment' => 'ambil', 'status' => 'diproses']);
        PickupCodeService::issue($order);

        Notification::assertSentTo(
            $order->fresh()->customer,
            OrderReadyForPickup::class,
            fn ($notification) => $notification->toWhatsApp($order->customer)->parameters[2] === $order->fresh()->pickup_code,
        );
    }

    public function test_a_completed_pickup_notifies_the_customer(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['fulfilment' => 'ambil', 'status' => 'diproses']);
        PickupCodeService::issue($order);
        $order->refresh();
        PickupCodeService::handOver($order, $order->pickup_code, User::factory()->create());

        Notification::assertSentTo($order->fresh()->customer, OrderCompleted::class);
    }

    public function test_a_cancelled_order_notifies_the_customer(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['status' => 'menunggu pembayaran']);
        OrderCancellation::apply($order, 'dibatalkan');

        Notification::assertSentTo($order->fresh()->customer, OrderCancelled::class);
    }

    public function test_updating_an_order_without_a_status_or_payment_change_notifies_nobody(): void
    {
        $order = $this->makeOrder();

        // Faked only after creation — `OrderConfirmed` from `makeOrder()`
        // itself is not what this test is checking.
        Notification::fake();
        $order->update(['note' => 'Catatan baru']);

        Notification::assertNothingSentTo($order->fresh()->customer);
    }
}

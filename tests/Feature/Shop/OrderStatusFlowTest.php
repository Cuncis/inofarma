<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Support\Presenters\ShopOrderPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<array{label: string, state: string}>
     */
    private function stepStates(Order $order): array
    {
        return array_map(fn (array $step) => ['label' => $step['label'], 'state' => $step['state']], ShopOrderPresenter::steps($order));
    }

    public function test_a_delivery_order_shows_the_five_delivery_steps(): void
    {
        $order = Order::factory()->create(['fulfilment' => 'antar', 'status' => 'siap dikirim']);

        $this->assertSame([
            ['label' => 'Pesanan Dibuat', 'state' => 'done'],
            ['label' => 'Pembayaran Diterima', 'state' => 'done'],
            ['label' => 'Siap Dikirim', 'state' => 'current'],
            ['label' => 'Dikirim', 'state' => 'pending'],
            ['label' => 'Diterima', 'state' => 'pending'],
        ], $this->stepStates($order));
    }

    public function test_a_pickup_order_has_no_shipping_steps(): void
    {
        $order = Order::factory()->pickup()->create(['status' => 'pembayaran diterima']);

        $this->assertSame([
            ['label' => 'Pesanan Dibuat', 'state' => 'done'],
            ['label' => 'Pembayaran Diterima', 'state' => 'current'],
            ['label' => 'Siap Diambil', 'state' => 'pending'],
            ['label' => 'Diambil', 'state' => 'pending'],
        ], $this->stepStates($order));
    }

    public function test_a_completed_order_has_every_step_done_and_a_cancelled_one_has_none(): void
    {
        $delivered = Order::factory()->selesai()->create(['fulfilment' => 'antar']);
        $cancelled = Order::factory()->create(['status' => 'dibatalkan']);

        $this->assertSame(['done', 'done', 'done', 'done', 'done'], array_column($this->stepStates($delivered), 'state'));
        $this->assertSame([], ShopOrderPresenter::steps($cancelled));
    }

    public function test_the_flows_are_exactly_the_agreed_ones(): void
    {
        $this->assertSame(['menunggu pembayaran', 'pembayaran diterima', 'siap dikirim', 'dikirim', 'selesai'], Order::FLOW_DELIVERY);
        $this->assertSame(['menunggu pembayaran', 'pembayaran diterima', 'siap diambil', 'selesai'], Order::FLOW_PICKUP);
        $this->assertSame(
            ['menunggu pembayaran', 'pembayaran diterima', 'siap dikirim', 'dikirim', 'selesai', 'dibatalkan', 'kedaluwarsa'],
            Order::statusesFor('antar'),
        );
    }

    public function test_a_paid_online_order_moves_to_payment_received(): void
    {
        $order = Order::factory()->create(['status' => 'menunggu pembayaran', 'payment_status' => 'belum bayar', 'payment_method' => 'online']);

        $this->assertTrue($order->is_cancellable_by_customer);

        $order->update(['status' => 'pembayaran diterima']);

        $this->assertFalse($order->fresh()->is_cancellable_by_customer);
    }
}

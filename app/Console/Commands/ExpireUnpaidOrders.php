<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Support\OrderCancellation;
use Illuminate\Console\Command;

/**
 * The 24-hour payment window ROADMAP.md Fase 6 asks for: an order still
 * `menunggu pembayaran` past its `expires_at` has its stock returned and is
 * marked `kedaluwarsa`, automatically, same as a customer's own cancellation
 * — see `OrderCancellation`.
 *
 * DOKU's own EXPIRED notification (`DokuPaymentService::applyToOrder()`)
 * usually gets there first for an order that actually opened a checkout
 * session; this sweep is the backstop for everything else — a session that
 * was never opened at all (DOKU was unreachable at checkout), or a
 * notification that never arrived.
 */
class ExpireUnpaidOrders extends Command
{
    protected $signature = 'pesanan:kadaluwarsakan';

    protected $description = 'Batalkan pesanan yang lewat batas waktu bayar dan kembalikan stoknya';

    public function handle(): int
    {
        $orders = Order::withoutGlobalScopes()
            ->where('status', 'menunggu pembayaran')
            ->where('payment_status', 'belum bayar')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($orders as $order) {
            OrderCancellation::apply($order, 'kedaluwarsa');
            $this->line("Kedaluwarsa: #{$order->number}");
        }

        $this->info("{$orders->count()} pesanan kedaluwarsa diproses.");

        return self::SUCCESS;
    }
}

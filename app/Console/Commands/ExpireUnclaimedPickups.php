<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Support\OrderCancellation;
use Illuminate\Console\Command;

/**
 * The pickup-window half of ROADMAP.md Fase 7.2 ("batas waktu ambil; lewat
 * batas → stok dikembalikan otomatis"), same shape as Fase 6's
 * `pesanan:kadaluwarsakan` for the payment window: an order still
 * `siap diambil` past `pickup_code_expires_at` has its stock returned via
 * `OrderCancellation` and is marked `kedaluwarsa` — reusing the exact same
 * mechanism rather than inventing a second way to give stock back.
 */
class ExpireUnclaimedPickups extends Command
{
    protected $signature = 'pesanan:kadaluwarsakan-pengambilan';

    protected $description = 'Batalkan pesanan ambil di toko yang lewat batas waktu ambil dan kembalikan stoknya';

    public function handle(): int
    {
        $orders = Order::withoutGlobalScopes()
            ->where('status', 'siap diambil')
            ->whereNotNull('pickup_code_expires_at')
            ->where('pickup_code_expires_at', '<', now())
            ->get();

        foreach ($orders as $order) {
            OrderCancellation::apply($order, 'kedaluwarsa');
            $this->line("Kedaluwarsa (tidak diambil): #{$order->number}");
        }

        $this->info("{$orders->count()} pesanan ambil kedaluwarsa diproses.");

        return self::SUCCESS;
    }
}

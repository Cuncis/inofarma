<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Notifications\Admin\PickupDeadlineApproaching;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * "Pesanan belum diambil mendekati batas" (ROADMAP.md Fase 8) — an hourly
 * nudge to branch staff, distinct from `OrderReadyForPickup` (which already
 * told the customer once). `orders.pickup_reminder_sent_at` stops the same
 * order from renotifying every run until it's actually collected or expires.
 */
class NotifyApproachingPickupDeadlines extends Command
{
    protected $signature = 'notifikasi:pengambilan-mendekati-batas';

    protected $description = 'Beri tahu staf cabang untuk pesanan ambil yang mendekati batas waktu';

    private const WARNING_HOURS = 6;

    public function handle(): int
    {
        $orders = Order::withoutGlobalScopes()
            ->where('status', 'siap diambil')
            ->whereNull('pickup_reminder_sent_at')
            ->whereNotNull('pickup_code_expires_at')
            ->whereBetween('pickup_code_expires_at', [now(), now()->addHours(self::WARNING_HOURS)])
            ->get();

        foreach ($orders as $order) {
            $staff = User::where('branch_id', $order->branch_id)->where('is_active', true)->get();

            if ($staff->isNotEmpty()) {
                Notification::send($staff, new PickupDeadlineApproaching($order));
            }

            $order->update(['pickup_reminder_sent_at' => now()]);
            $this->line("Diberitahukan: pesanan #{$order->number}");
        }

        $this->info("{$orders->count()} pesanan mendekati batas ambil diberitahukan.");

        return self::SUCCESS;
    }
}

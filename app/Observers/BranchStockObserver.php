<?php

namespace App\Observers;

use App\Models\BranchStock;
use App\Models\User;
use App\Notifications\Admin\LowStock;
use Illuminate\Support\Facades\Notification;

/**
 * "Stok menipis" (ROADMAP.md Fase 8). Fires only on the transition *into*
 * low stock — comparing the row's pre-save quantities against its
 * `reorder_point` — so a product that's been sitting below the threshold
 * for a week doesn't renotify branch staff on every subsequent sale.
 */
class BranchStockObserver
{
    public function updated(BranchStock $stock): void
    {
        if (! $stock->reorder_point || $stock->reorder_point <= 0) {
            return;
        }

        if (! $stock->wasChanged(['quantity', 'reserved_quantity'])) {
            return;
        }

        $wasAvailable = max(
            (int) $stock->getOriginal('quantity') - (int) $stock->getOriginal('reserved_quantity'),
            0,
        );

        $wasLow = $wasAvailable <= $stock->reorder_point;

        if ($wasLow || ! $stock->is_low) {
            return;
        }

        $staff = User::where('branch_id', $stock->branch_id)->where('is_active', true)->get();

        if ($staff->isEmpty()) {
            return;
        }

        Notification::send($staff, new LowStock($stock));
    }
}

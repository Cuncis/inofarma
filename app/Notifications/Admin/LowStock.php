<?php

namespace App\Notifications\Admin;

use App\Models\BranchStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Stok menipis" (ROADMAP.md Fase 8) — fired by
 * `App\Observers\BranchStockObserver` the moment a branch's available stock
 * crosses at/below its `reorder_point`, not on every write after that (see
 * the observer for the before/after comparison that prevents a repeat ping
 * on every subsequent sale of an already-low product).
 */
class LowStock extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly BranchStock $stock) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $stock = $this->stock;

        return [
            'title' => 'Stok menipis',
            'body' => "{$stock->product->name}: tersisa {$stock->available} (batas {$stock->reorder_point})",
            'link' => route('admin.inventaris.stok.show', $stock->branch->code),
        ];
    }
}

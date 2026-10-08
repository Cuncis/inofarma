<?php

namespace App\Support\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Support\Auth\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Makes a branch's batches for one product match a counted list exactly.
 *
 * This is the stock-opname-from-a-spreadsheet half of inventory: the list is
 * the whole truth for this product at this branch. Batches in the list get the
 * listed quantity and expiry; batches the branch holds that are not in the list
 * are set to zero (kept, not deleted, so their movement history stays
 * readable). Every change is written as a `penyesuaian` movement.
 *
 * Reserved stock (held for orders not yet picked up) is never touched, so a
 * count below what is already promised to customers is refused rather than
 * silently over-selling.
 *
 * Queries bypass `BranchScope` for the same reason as `StockAllocator`.
 */
class StockReplacer
{
    /**
     * @param  list<array{batch_number: string, expires_at: string, quantity: int, cost_price?: int|null, received_at?: string|null}>  $batches
     * @param  array{reorder_point?: int, price_override?: int, is_listed?: bool}  $settings
     * @return array{batches: int, zeroed: int}
     *
     * @throws RuntimeException when the new total is below the reserved quantity
     */
    public function replace(
        Branch $branch,
        Product $product,
        array $batches,
        array $settings = [],
        ?int $userId = null,
        ?string $note = null,
    ): array {
        return DB::transaction(function () use ($branch, $product, $batches, $settings, $userId, $note) {
            $stock = BranchStock::withoutGlobalScope(BranchScope::class)->lockForUpdate()->firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_point' => 20, 'is_listed' => true],
            );

            $newTotal = array_sum(array_column($batches, 'quantity'));

            if ($newTotal < $stock->reserved_quantity) {
                throw new RuntimeException(
                    "Jumlah baru {$newTotal} lebih kecil dari stok yang sedang dipesan ({$stock->reserved_quantity})."
                );
            }

            $existing = InventoryBatch::withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branch->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn (InventoryBatch $batch) => mb_strtolower($batch->batch_number));

            $running = (int) $existing->sum('quantity');
            $zeroed = 0;

            foreach ($batches as $entry) {
                $key = mb_strtolower($entry['batch_number']);
                $batch = $existing->pull($key) ?? new InventoryBatch([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'batch_number' => $entry['batch_number'],
                ]);

                $delta = $entry['quantity'] - ($batch->exists ? $batch->quantity : 0);

                $batch->expires_at = $entry['expires_at'];
                $batch->cost_price = $entry['cost_price'] ?? $batch->cost_price;
                $batch->received_at = $entry['received_at'] ?? $batch->received_at ?? now();
                $batch->quantity = $entry['quantity'];
                $batch->save();

                $running += $delta;
                $this->recordMovement($branch, $product, $batch, $delta, $running, $userId, $note);
            }

            foreach ($existing as $batch) {
                if ($batch->quantity === 0) {
                    continue;
                }

                $delta = -$batch->quantity;
                $batch->update(['quantity' => 0]);
                $running += $delta;
                $zeroed++;
                $this->recordMovement($branch, $product, $batch, $delta, $running, $userId, $note);
            }

            $stock->update(['quantity' => $newTotal] + $settings);

            return ['batches' => count($batches), 'zeroed' => $zeroed];
        });
    }

    private function recordMovement(
        Branch $branch,
        Product $product,
        InventoryBatch $batch,
        int $delta,
        int $balanceAfter,
        ?int $userId,
        ?string $note,
    ): void {
        if ($delta === 0) {
            return;
        }

        InventoryMovement::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'inventory_batch_id' => $batch->id,
            'type' => 'penyesuaian',
            'quantity' => $delta,
            'balance_after' => $balanceAfter,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }
}

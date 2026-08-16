<?php

namespace App\Support\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * A direct correction to a branch's stock count, not tied to any one batch.
 *
 * For stock opname, damage write-offs, and other adjustments where which
 * physical batch is involved either doesn't matter or isn't known precisely.
 * Receiving new purchased stock should go through `StockAllocator::receive()`
 * instead — a purchase has a batch number and an expiry date, and losing that
 * at the point of entry is not recoverable later.
 */
class StockAdjuster
{
    /**
     * @throws InsufficientStockException when a negative delta would take the
     *                                    branch below zero
     */
    public function adjust(
        Branch $branch,
        Product $product,
        int $delta,
        string $type,
        ?int $userId = null,
        ?string $note = null,
    ): BranchStock {
        return DB::transaction(function () use ($branch, $product, $delta, $type, $userId, $note) {
            $stock = BranchStock::lockForUpdate()->firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_point' => 20, 'is_listed' => true],
            );

            $newQuantity = $stock->quantity + $delta;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(abs($delta), $stock->quantity);
            }

            $stock->update(['quantity' => $newQuantity]);

            InventoryMovement::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $delta,
                'balance_after' => $newQuantity,
                'note' => $note,
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }
}

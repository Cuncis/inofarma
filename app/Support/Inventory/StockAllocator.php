<?php

namespace App\Support\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Moves stock in or out of a branch, batch by batch.
 *
 * `consume()` is the FEFO half: it takes from whichever batch expires soonest
 * first, because leaving an old batch on the shelf while a newer one sells
 * means the old one expires there. `receive()` is the other half — stock
 * arriving with a batch number and an expiry date, whether from a supplier or
 * from another branch's transfer.
 *
 * Both run inside a transaction with the branch's stock row locked, so two
 * concurrent sales (or a sale racing a transfer) cannot both succeed against
 * stock that only exists once.
 */
class StockAllocator
{
    /**
     * Take `$quantity` units of a product out of a branch, oldest-expiry-first.
     *
     * @return list<array{batch_id: int, batch_number: string, expires_at: string, quantity: int}>
     *
     * @throws InsufficientStockException when the branch does not have enough
     */
    public function consume(
        Branch $branch,
        Product $product,
        int $quantity,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): array {
        return DB::transaction(function () use ($branch, $product, $quantity, $type, $reference, $userId, $note) {
            $stock = BranchStock::where('branch_id', $branch->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $available = $stock ? $stock->quantity - $stock->reserved_quantity : 0;

            if ($available < $quantity) {
                throw new InsufficientStockException($quantity, max($available, 0));
            }

            $batches = InventoryBatch::where('branch_id', $branch->id)
                ->where('product_id', $product->id)
                ->fefo()
                ->lockForUpdate()
                ->get();

            if ($batches->sum('quantity') < $quantity) {
                throw new RuntimeException(
                    "Data stok {$product->name} di {$branch->name} tidak sinkron: ".
                    'ringkasan cabang menyatakan cukup, tapi batch fisiknya kurang. '.
                    'Perlu stok opname sebelum transaksi ini bisa lanjut.'
                );
            }

            $remaining = $quantity;
            $runningBalance = $stock->quantity;
            $manifest = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($batch->quantity, $remaining);
                $batch->decrement('quantity', $take);
                $remaining -= $take;
                $runningBalance -= $take;

                $manifest[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expires_at' => $batch->expires_at->toDateString(),
                    'quantity' => $take,
                ];

                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => -$take,
                    'balance_after' => $runningBalance,
                    'reference_type' => $reference?->getMorphClass(),
                    'reference_id' => $reference?->getKey(),
                    'note' => $note,
                    'user_id' => $userId,
                ]);
            }

            $stock->decrement('quantity', $quantity);

            return $manifest;
        });
    }

    /**
     * Add stock to a branch from a manifest of batches — either freshly
     * purchased or arriving from another branch's transfer.
     *
     * @param  list<array{batch_number: string, expires_at: string, quantity: int, cost_price?: int|null}>  $manifest
     */
    public function receive(
        Branch $branch,
        Product $product,
        array $manifest,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): BranchStock {
        return DB::transaction(function () use ($branch, $product, $manifest, $type, $reference, $userId, $note) {
            $stock = BranchStock::lockForUpdate()->firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_point' => 20, 'is_listed' => true],
            );

            $total = 0;

            foreach ($manifest as $entry) {
                $batch = InventoryBatch::lockForUpdate()->firstOrNew([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'batch_number' => $entry['batch_number'],
                ]);

                $batch->expires_at = $entry['expires_at'];
                $batch->cost_price = $entry['cost_price'] ?? $batch->cost_price;
                $batch->received_at ??= now();
                $batch->quantity = ($batch->exists ? $batch->quantity : 0) + $entry['quantity'];
                $batch->save();

                $total += $entry['quantity'];

                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => $entry['quantity'],
                    'balance_after' => $stock->quantity + $total,
                    'reference_type' => $reference?->getMorphClass(),
                    'reference_id' => $reference?->getKey(),
                    'note' => $note,
                    'user_id' => $userId,
                ]);
            }

            $stock->increment('quantity', $total);

            return $stock->fresh();
        });
    }
}

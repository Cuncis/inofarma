<?php

namespace App\Support\Inventory;

use App\Models\StockTransfer;
use App\Support\CodeSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The three-step lifecycle of a stock transfer: diminta → dikirim → diterima.
 *
 * Shipping picks FEFO batches at the origin the same way a sale would, and
 * writes their batch numbers and expiry dates onto the transfer so receiving
 * can recreate those exact batches at the destination — the stock's expiry
 * information has to survive the trip, or FEFO breaks at the branch it lands in.
 */
class StockTransferManager
{
    public function __construct(private readonly StockAllocator $allocator) {}

    /**
     * @param  array<string, mixed>  $attributes  from_branch_id, to_branch_id, product_id, quantity, note
     */
    public function request(array $attributes, ?int $userId = null): StockTransfer
    {
        return StockTransfer::create([
            ...$attributes,
            'code' => CodeSequence::next(StockTransfer::query(), 'code', 'TRF-'),
            'status' => 'diminta',
            'requested_by' => $userId,
        ]);
    }

    public function ship(StockTransfer $transfer, ?int $userId = null): StockTransfer
    {
        if (! $transfer->can_be_shipped) {
            throw new RuntimeException("Transfer {$transfer->code} sudah diproses dan tidak bisa dikirim ulang.");
        }

        return DB::transaction(function () use ($transfer, $userId) {
            $manifest = $this->allocator->consume(
                $transfer->fromBranch,
                $transfer->product,
                $transfer->quantity,
                'transfer keluar',
                $transfer,
                $userId,
                "Transfer {$transfer->code} ke {$transfer->toBranch->name}",
            );

            $transfer->update([
                'status' => 'dikirim',
                'batches_shipped' => $manifest,
                'shipped_at' => now(),
            ]);

            return $transfer;
        });
    }

    public function receive(StockTransfer $transfer, ?int $userId = null): StockTransfer
    {
        if (! $transfer->can_be_received) {
            throw new RuntimeException("Transfer {$transfer->code} belum dikirim atau sudah diterima.");
        }

        return DB::transaction(function () use ($transfer, $userId) {
            $this->allocator->receive(
                $transfer->toBranch,
                $transfer->product,
                $transfer->batches_shipped,
                'transfer masuk',
                $transfer,
                $userId,
                "Transfer {$transfer->code} dari {$transfer->fromBranch->name}",
            );

            $transfer->update(['status' => 'diterima', 'received_at' => now()]);

            return $transfer;
        });
    }

    /**
     * Cancel a request before anything has physically moved. Once a transfer
     * has shipped, cancelling would mean reversing a FEFO pick — receive it
     * back at the origin as its own transfer instead of trying to undo this one.
     */
    public function cancel(StockTransfer $transfer): StockTransfer
    {
        if (! $transfer->can_be_cancelled) {
            throw new RuntimeException("Transfer {$transfer->code} sudah dikirim dan tidak bisa dibatalkan.");
        }

        $transfer->update(['status' => 'dibatalkan', 'cancelled_at' => now()]);

        return $transfer;
    }
}

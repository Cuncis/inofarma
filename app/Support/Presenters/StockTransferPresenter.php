<?php

namespace App\Support\Presenters;

use App\Models\StockTransfer;
use App\Support\AdminOptions;

class StockTransferPresenter
{
    /**
     * @param  iterable<StockTransfer>  $transfers
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $transfers): array
    {
        return collect($transfers)->map(fn (StockTransfer $transfer) => self::toArray($transfer))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(StockTransfer $transfer): array
    {
        return [
            'id' => $transfer->code,
            'fromBranch' => $transfer->fromBranch?->code,
            'fromBranchName' => $transfer->fromBranch?->name,
            'toBranch' => $transfer->toBranch?->code,
            'toBranchName' => $transfer->toBranch?->name,
            'product' => $transfer->product?->sku,
            'productName' => $transfer->product?->name,
            'quantity' => $transfer->quantity,
            'status' => AdminOptions::toLabel(AdminOptions::STOCK_TRANSFER_STATUSES, $transfer->status),
            'requestedBy' => $transfer->requestedBy?->name,
            'note' => $transfer->note,
            'requestedAt' => $transfer->created_at?->translatedFormat('d M Y, H:i'),
            'shippedAt' => $transfer->shipped_at?->translatedFormat('d M Y, H:i'),
            'receivedAt' => $transfer->received_at?->translatedFormat('d M Y, H:i'),
            'cancelledAt' => $transfer->cancelled_at?->translatedFormat('d M Y, H:i'),
            'canBeShipped' => $transfer->can_be_shipped,
            'canBeReceived' => $transfer->can_be_received,
            'canBeCancelled' => $transfer->can_be_cancelled,
        ];
    }
}

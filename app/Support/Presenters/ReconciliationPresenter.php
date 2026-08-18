<?php

namespace App\Support\Presenters;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;

/**
 * "Rekonsiliasi harian, dipecah per cabang untuk setoran" (ROADMAP.md Fase
 * 6) — how much each branch actually collected, by day, for reconciling
 * against what lands in the bank. `daily()` reads aggregated `orders` rows;
 * `log()` is the raw `payments` attempt-by-attempt trail underneath it, for
 * chasing down a pending/failed payment a branch is asking about.
 */
class ReconciliationPresenter
{
    /**
     * @param  iterable<Order>  $rows  grouped by branch_id + DATE(paid_at), with `branch` eager-loaded
     * @return list<array<string, mixed>>
     */
    public static function daily(iterable $rows): array
    {
        return collect($rows)->map(fn (Order $row) => [
            'branch' => $row->branch?->name ?? '—',
            'tanggal' => Carbon::parse($row->getAttribute('tanggal'))->translatedFormat('d M Y'),
            'jumlahPesanan' => (int) $row->getAttribute('jumlah_pesanan'),
            'total' => (int) $row->getAttribute('total'),
        ])->values()->all();
    }

    /**
     * @param  iterable<Payment>  $payments
     * @return list<array<string, mixed>>
     */
    public static function log(iterable $payments): array
    {
        return collect($payments)->map(fn (Payment $payment) => [
            'invoiceNumber' => $payment->invoice_number,
            'orderNumber' => $payment->order?->number,
            'branch' => $payment->order?->branch?->name,
            'status' => ucfirst($payment->status),
            'channel' => $payment->channel,
            'amount' => $payment->amount,
            'createdAt' => $payment->created_at?->translatedFormat('d M Y, H:i'),
        ])->values()->all();
    }
}

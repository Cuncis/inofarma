<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Payments\DokuPaymentService;
use App\Support\Presenters\ReconciliationPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Daily reconciliation, per branch, plus the raw gateway attempt log
 * underneath it (Fase 6). Otherwise read-only — every write to a `Payment`
 * happens through `DokuPaymentService`, either the webhook (the normal path)
 * or `checkStatus()` below (a manual nudge for one stuck attempt, same
 * underlying `applyNotification()` either way) — or `InvoiceController::refund()`.
 */
class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $from = $request->date('dari') ?? now()->subDays(6)->startOfDay();
        $to = $request->date('sampai') ?? now()->endOfDay();

        $daily = Order::query()
            ->with('branch')
            ->where('payment_status', 'lunas')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('branch_id, DATE(paid_at) as tanggal, COUNT(*) as jumlah_pesanan, SUM(grand_total) as total')
            ->groupBy('branch_id', 'tanggal')
            ->orderByDesc('tanggal')
            ->get();

        $log = Payment::with(['order.branch'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return Inertia::render('Admin/PaymentReconciliation', [
            'daily' => ReconciliationPresenter::daily($daily),
            'log' => ReconciliationPresenter::log($log),
            'grandTotal' => (int) $daily->sum('total'),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function checkStatus(Payment $payment): RedirectResponse
    {
        try {
            $result = DokuPaymentService::make()->reconcile($payment);
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->with('error', 'Gagal menghubungi DOKU. Coba lagi sebentar lagi.');
        }

        if (! $result) {
            return back()->with('error', 'DOKU tidak mengenali pembayaran ini.');
        }

        // `applyNotification()` returns the payment either way — a "pending"
        // result just means DOKU itself has nothing new yet, not an error,
        // but it isn't the "updated" success case either.
        if ($result->status === 'pending') {
            return back()->with('error', "Menurut DOKU, pembayaran #{$payment->invoice_number} masih menunggu pembayaran.");
        }

        return back()->with('success', "Status pembayaran #{$payment->invoice_number} diperbarui: {$result->status}.");
    }
}

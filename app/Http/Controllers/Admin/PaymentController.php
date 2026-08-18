<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Presenters\ReconciliationPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Daily reconciliation, per branch, plus the raw gateway attempt log
 * underneath it (Fase 6). Read-only — every write to a `Payment` happens
 * through `DokuPaymentService` (webhook) or `InvoiceController::refund()`.
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
}

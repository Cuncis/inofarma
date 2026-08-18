<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\AuditLogger;
use App\Support\Presenters\InvoicePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only, save one thing: a faktur is an `Order` rendered as an invoice,
 * not a record of its own (see `InvoicePresenter`). There is nothing here to
 * create, edit or delete — that already happens on `/admin/pesanan`.
 *
 * `refund()` is the exception, and it only ever *records* a refund (Fase 6)
 * — it does not call DOKU's refund API. DOKU's refund endpoint only covers
 * card payments; VA/e-wallet/QRIS refunds go back to the customer by other
 * means the pharmacy already has (bank transfer, etc.), so an admin records
 * that it happened rather than this app pretending to trigger it uniformly
 * across every channel.
 */
class InvoiceController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with('customer')->orderByDesc('created_at')->get();

        return Inertia::render('Admin/InvoiceList', [
            'invoices' => InvoicePresenter::collection($orders),
        ]);
    }

    public function show(string $order): Response
    {
        return Inertia::render('Admin/InvoiceDetail', [
            'invoice' => InvoicePresenter::withLines($this->find($order)),
        ]);
    }

    public function refund(Request $request, string $order): RedirectResponse
    {
        $record = $this->find($order);

        if ($record->payment_status !== 'lunas') {
            return back()->with('error', 'Hanya pesanan yang sudah lunas yang bisa direfund.');
        }

        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ], [], ['note' => 'catatan']);

        $payment = $record->latest_payment;

        $payment?->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_note' => $data['note'],
        ]);

        $record->update(['payment_status' => 'refund']);

        AuditLogger::log('faktur.refund', $record, ['payment_status' => 'lunas'], [
            'payment_status' => 'refund', 'note' => $data['note'],
        ], branchId: $record->branch_id);

        return redirect()
            ->route('admin.faktur.show', $record->number)
            ->with('success', "Refund untuk #{$record->number} dicatat.");
    }

    private function find(string $order): Order
    {
        return Order::with(['customer', 'branch', 'items', 'payments'])
            ->where('number', $order)
            ->firstOr(fn () => abort(404, 'Faktur tidak ditemukan.'));
    }
}

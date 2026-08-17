<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Presenters\InvoicePresenter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only: a faktur is an `Order` rendered as an invoice, not a record of
 * its own (see `InvoicePresenter`). There is nothing here to create, edit or
 * delete — that already happens on `/admin/pesanan`.
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
        $record = Order::with(['customer', 'branch', 'items'])
            ->where('number', $order)
            ->firstOr(fn () => abort(404, 'Faktur tidak ditemukan.'));

        return Inertia::render('Admin/InvoiceDetail', [
            'invoice' => InvoicePresenter::withLines($record),
        ]);
    }
}

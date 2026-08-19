<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\OrderCancellation;
use App\Support\Presenters\ShopOrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A signed-in customer's own orders — history, tracking, and self-service
 * cancellation before the pharmacy starts processing. Every query is scoped
 * through `$request->user('customer')->orders()`, never `Order::query()`
 * directly, so a customer can never load another customer's order by
 * guessing a number.
 */
class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user('customer')->orders()
            ->with(['branch'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Shop/OrderHistory', [
            'orders' => ShopOrderPresenter::collection($orders),
        ]);
    }

    /** Items, total, and the Bayar/Batalkan actions — the page an order's own row (or a payment redirect) lands on. */
    public function show(Request $request, string $order): Response
    {
        return Inertia::render('Shop/OrderDetail', [
            'order' => ShopOrderPresenter::toArray($this->find($request, $order)),
        ]);
    }

    /** The shipment/pickup timeline — read-only, no actions, reached from `show()` via "Lacak Pesanan". */
    public function track(Request $request, string $order): Response
    {
        return Inertia::render('Shop/TrackOrder', [
            'order' => ShopOrderPresenter::toArray($this->find($request, $order)),
        ]);
    }

    public function cancel(Request $request, string $order): RedirectResponse
    {
        $record = $this->find($request, $order);

        if (! $record->is_cancellable_by_customer) {
            return back()->with('error', 'Pesanan ini sudah diproses dan tidak bisa dibatalkan sendiri.');
        }

        OrderCancellation::apply($record, 'dibatalkan');

        return redirect()->route('ui.order-history')->with('success', "Pesanan #{$record->number} dibatalkan.");
    }

    private function find(Request $request, string $number): Order
    {
        return $request->user('customer')->orders()
            ->with(['branch', 'items.product', 'coupon'])
            ->where('number', $number)
            ->firstOr(fn () => abort(404, 'Pesanan tidak ditemukan.'));
    }
}

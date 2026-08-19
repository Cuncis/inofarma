<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Payments\DokuPaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Opens (or re-opens) a DOKU Checkout session for one of the signed-in
 * customer's own orders. `CheckoutController::store()` calls the same
 * `DokuPaymentService` for the first attempt; this is the retry path — a
 * session expired, or DOKU itself was unreachable when the order was placed.
 */
class PaymentController extends Controller
{
    public function create(Request $request, string $order): Response
    {
        $record = $request->user('customer')->orders()
            ->where('number', $order)
            ->firstOr(fn () => abort(404, 'Pesanan tidak ditemukan.'));

        if ($record->payment_status !== 'belum bayar' || $record->status !== 'menunggu pembayaran') {
            return back()->with('error', 'Pesanan ini sudah tidak menunggu pembayaran.');
        }

        try {
            $payment = DokuPaymentService::make()->createForOrder($record);
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->with('error', 'Gagal membuka halaman pembayaran. Coba lagi sebentar lagi.');
        }

        return Inertia::location($payment->checkout_url);
    }
}

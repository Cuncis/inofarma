<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Support\Cart\CartManager;
use App\Support\Cart\DeliveryPricing;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAllocator;
use App\Support\OrderNumber;
use App\Support\Payments\DokuPaymentService;
use App\Support\Presenters\CartPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Turns a cart into an `Order`. Stock is consumed immediately through
 * `StockAllocator` (FEFO, batch-tracked) rather than held in
 * `branch_stocks.reserved_quantity` — that column stays for Fase 6/7's
 * payment-expiry and pickup-expiry auto-release, which need to give stock
 * back without ever having taken it. Immediate consumption is the mechanism
 * that already exists and is already tested; see ROADMAP.md 5.3.
 *
 * Payment (Fase 6): "online" opens a DOKU Checkout session and sends the
 * customer's browser there — DOKU's own hosted page is where a shopper
 * actually picks VA/e-wallet/QRIS/card, not a picker built here. "Tunai" is
 * cash at pickup, only offered for `fulfilment: ambil` — see `.ai/rules` for
 * why delivery orders can't choose it.
 */
class CheckoutController extends Controller
{
    /** Fase 0's PPN rate/exemptions are still an open decision — no tax is charged until that lands. */
    private const TAX_RATE = 0;

    /** A stand-in for real pickup-time scheduling (Fase 7); a shopper just says roughly when. */
    private const PICKUP_ETA_OPTIONS = ['Hari ini', 'Besok'];

    public function show(CartManager $cart): Response|RedirectResponse
    {
        $data = $cart->current();

        if (! $data['branch'] || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        return Inertia::render('Shop/Checkout', [
            'cart' => CartPresenter::toArray($data),
            'pickupEtaOptions' => self::PICKUP_ETA_OPTIONS,
            'deliveryFee' => DeliveryPricing::FLAT_FEE,
        ]);
    }

    public function store(Request $request, CartManager $cart): RedirectResponse
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'fulfilment' => ['required', 'in:antar,ambil'],
            'paymentMethod' => ['required', 'in:online,Tunai'],
            'pickupEta' => ['required_if:fulfilment,ambil', 'nullable', 'in:'.implode(',', self::PICKUP_ETA_OPTIONS)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['paymentMethod'] === 'Tunai' && $validated['fulfilment'] === 'antar') {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Bayar di tempat hanya tersedia untuk pesanan Ambil di Toko.',
            ]);
        }

        $data = $cart->current();
        $branch = $data['branch'];

        if (! $branch || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        if ($validated['fulfilment'] === 'antar') {
            if (! $branch->supports_delivery) {
                throw ValidationException::withMessages(['fulfilment' => "{$branch->name} tidak melayani antar."]);
            }

            if (! $data['address']) {
                throw ValidationException::withMessages(['address' => 'Pilih alamat pengiriman terlebih dahulu.']);
            }

            if (! DeliveryPricing::isWithinRadius($branch, $data['address'])) {
                throw ValidationException::withMessages([
                    'address' => "Alamat ini di luar radius antar {$branch->name} ({$branch->delivery_radius_km} km).",
                ]);
            }
        } elseif (! $branch->supports_pickup) {
            throw ValidationException::withMessages(['fulfilment' => "{$branch->name} tidak melayani ambil di tempat."]);
        }

        $order = DB::transaction(fn () => $this->placeOrder($customer, $data, $branch, $validated));

        $cart->clear();

        if ($validated['paymentMethod'] === 'Tunai') {
            return redirect()
                ->route('ui.order-successful', ['nomor' => $order->number])
                ->with('success', "Pesanan #{$order->number} berhasil dibuat.");
        }

        try {
            $payment = DokuPaymentService::make()->createForOrder($order);
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()
                ->route('ui.track-order', $order->number)
                ->with('error', "Pesanan #{$order->number} berhasil dibuat, tapi gagal membuka halaman pembayaran. Coba lagi dari halaman ini.");
        }

        return Inertia::location($payment->checkout_url);
    }

    /**
     * @param  array{branch: Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}  $data
     * @param  array{fulfilment: string, paymentMethod: string, pickupEta: ?string, note: ?string}  $validated
     */
    private function placeOrder(Customer $customer, array $data, Branch $branch, array $validated): Order
    {
        $coupon = $data['coupon'];
        $subtotal = CartManager::subtotal($branch, $data['lines']);
        $discount = $coupon ? $coupon->discountFor($subtotal) : 0;
        $freeShipping = $coupon?->is_free_shipping ?? false;
        $shipping = $validated['fulfilment'] === 'antar' ? DeliveryPricing::fee($freeShipping) : 0;
        $tax = (int) round(($subtotal - $discount) * self::TAX_RATE);
        $grandTotal = max($subtotal - $discount + $shipping + $tax, 0);

        $address = $data['address'];
        $note = trim(($validated['note'] ?? '').($validated['fulfilment'] === 'ambil' ? "\nPerkiraan ambil: {$validated['pickupEta']}" : ''));

        $order = Order::create([
            'number' => OrderNumber::generate($branch),
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'coupon_id' => $coupon?->id,
            'fulfilment' => $validated['fulfilment'],
            'status' => 'menunggu pembayaran',
            'payment_method' => $validated['paymentMethod'],
            'payment_status' => 'belum bayar',
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'shipping_total' => $shipping,
            'tax_total' => $tax,
            'grand_total' => $grandTotal,
            'recipient_name' => $address?->recipient_name ?? $customer->name,
            'recipient_phone' => $address?->phone ?? $customer->phone,
            'shipping_address' => $address?->full_address,
            'shipping_latitude' => $address?->latitude,
            'shipping_longitude' => $address?->longitude,
            'note' => $note !== '' ? $note : null,
            'expires_at' => now()->addHours(24),
        ]);

        $allocator = new StockAllocator;

        foreach ($data['lines'] as $line) {
            $product = $line['product'];
            $stock = $product->stockAt($branch);
            $price = $stock?->effective_price ?? $product->price;

            try {
                $manifest = $allocator->consume($branch, $product, $line['quantity'], 'penjualan', $order);
            } catch (InsufficientStockException $exception) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$product->name} baru saja berubah: tersisa {$exception->available}, diminta {$exception->requested}.",
                ]);
            }

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $price,
                'quantity' => $line['quantity'],
                'line_total' => $price * $line['quantity'],
                'batches_consumed' => $manifest,
            ]);
        }

        $coupon?->increment('used_count');

        return $order;
    }
}

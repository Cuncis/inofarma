<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Support\Cart\CartManager;
use App\Support\Cart\DraftAddress;
use App\Support\CodeSequence;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAllocator;
use App\Support\LocationPreference;
use App\Support\OrderNumber;
use App\Support\Payments\DokuPaymentService;
use App\Support\Presenters\CartPresenter;
use App\Support\Presenters\StorefrontBranchPresenter;
use App\Support\Shipping\ShippingQuoteService;
use App\Support\Shipping\ShippingUnavailableException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
 * actually picks VA/e-wallet/QRIS/card, not a picker built here. Online is the
 * only way to pay: there is no cash on delivery and no cash at pickup.
 */
class CheckoutController extends Controller
{
    /** Fase 0's PPN rate/exemptions are still an open decision — no tax is charged until that lands. */
    private const TAX_RATE = 0;

    public function show(Request $request, CartManager $cart): Response|RedirectResponse
    {
        $data = $cart->current();

        if (! $data['branch'] || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        if (collect($data['lines'])->contains(fn (array $line) => $line['quantity'] > $line['product']->purchaseLimit())) {
            return redirect()->route('ui.cart')->with('error', Product::OVER_LIMIT_MESSAGE);
        }

        return Inertia::render('Shop/Checkout', [
            'cart' => CartPresenter::toArray($data),
            'isGuest' => ! $request->user('customer'),
            'canDeliver' => CartManager::deliveryBranch($data['branch'], $data['lines']) !== null,
            'provinces' => fn () => Region::query()->where('level', 1)->orderBy('name')->get(['code', 'name'])
                ->map(fn (Region $region) => ['code' => $region->code, 'name' => $region->name]),
            // A function, so a partial reload after the shopper shares a location
            // (see PickupLocationModal) re-sorts the branches by distance.
            'pickupBranches' => fn () => $this->pickupBranches($request, $data['address']),
        ]);
    }

    public function store(Request $request, CartManager $cart): RedirectResponse|SymfonyResponse
    {
        $customer = $request->user('customer');

        $data = $cart->current();
        $branch = $data['branch'];

        if (! $branch || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        // One form for everyone: a guest also fills in who they are and agrees
        // to the privacy policy (an account is created for them below), and
        // anyone without a saved address types one right here.
        $isGuest = ! $customer;
        $needsAddress = ! $data['address'];
        $addressRequired = Rule::requiredIf($needsAddress && $request->input('fulfilment') === 'antar');

        $validated = $request->validate([
            'fulfilment' => ['required', 'in:antar,ambil'],
            'paymentMethod' => ['required', 'in:online'],
            'pickupBranchId' => ['required_if:fulfilment,ambil', 'nullable', 'string'],
            'courier' => ['nullable', 'array'],
            'courier.courierCompany' => ['nullable', 'string'],
            'courier.courierType' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
            'name' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:255'],
            'phone' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => [Rule::requiredIf($isGuest), 'nullable', 'email', 'max:255', 'unique:customers,email'],
            'consent' => $isGuest ? ['accepted'] : ['nullable'],
            'addressLine' => [$addressRequired, 'nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota' => [$addressRequired, 'nullable', 'string', 'max:255'],
            'provinsi' => [$addressRequired, 'nullable', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk untuk melanjutkan.',
            'consent.accepted' => 'Anda harus menyetujui Kebijakan Privasi untuk melanjutkan.',
        ], [
            'addressLine' => 'alamat lengkap', 'kota' => 'kota/kabupaten', 'provinsi' => 'provinsi',
        ]);

        CartManager::assertWithinPurchaseLimit($data['lines']);

        $address = $data['address'];

        if (! $address && DraftAddress::isComplete($validated)) {
            $address = DraftAddress::make([
                ...$validated,
                'recipientName' => $customer?->name ?? $validated['name'],
                'recipientPhone' => $customer?->phone ?? $validated['phone'],
            ]);
        }

        if ($validated['fulfilment'] === 'antar') {
            $branch = CartManager::deliveryBranch($branch, $data['lines']);

            if (! $branch) {
                throw ValidationException::withMessages(['fulfilment' => 'Pesanan ini belum bisa diantar. Pilih ambil di toko.']);
            }

            if (! $address) {
                throw ValidationException::withMessages(['address' => 'Isi alamat pengiriman terlebih dahulu.']);
            }

            if (empty($validated['courier']['courierCompany']) || empty($validated['courier']['courierType'])) {
                throw ValidationException::withMessages(['courier' => 'Pilih kurir terlebih dahulu.']);
            }

            // Re-quoted here rather than trusted from the client — the same
            // reason `grand_total` is always computed server-side, never
            // taken from a form field. A price a customer saw a minute ago
            // may no longer be the cheapest, or may no longer exist at all.
            try {
                $quotes = ShippingQuoteService::make()->quote($branch, $address, $data['lines']);
            } catch (ShippingUnavailableException $exception) {
                report($exception);

                throw ValidationException::withMessages(['courier' => $exception->customerMessage]);
            } catch (RuntimeException $exception) {
                report($exception);
                $quotes = [];
            }

            $chosen = collect($quotes)->first(
                fn (array $quote) => $quote['courierCompany'] === $validated['courier']['courierCompany']
                    && $quote['courierType'] === $validated['courier']['courierType']
            );

            if (! $chosen) {
                throw ValidationException::withMessages([
                    'courier' => 'Pilihan kurir tidak lagi tersedia, muat ulang halaman checkout.',
                ]);
            }

            $validated['courierQuote'] = $chosen;
        } else {
            // Pickup is chosen here, not on the product page: the order is placed
            // at the branch the shopper collects it from, which may not be the
            // branch the cart was started with.
            $branch = $this->pickupBranchFor($validated['pickupBranchId']);
        }

        $cart->assertCouponStillUsable($data['coupon'], $branch, $data['lines']);

        // Everything that can be refused has been; only now does a guest become
        // a real account, so a bad address never leaves a stray customer behind.
        if ($isGuest) {
            $customer = $this->registerGuest($request, $cart, $validated);
        }

        if ($address && ! $address->exists) {
            $address->forceFill([
                'customer_id' => $customer->id,
                'recipient_name' => $customer->name,
                'phone' => $customer->phone,
                'is_default' => ! $customer->addresses()->exists(),
            ])->save();
        }

        if ($address) {
            $cart->setAddress($address);
        }

        $data = $cart->current();

        $order = DB::transaction(fn () => $this->placeOrder($customer, $data, $branch, $validated));

        $cart->clear();

        try {
            $payment = DokuPaymentService::make()->createForOrder($order);
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()
                ->route('ui.pesanan.show', $order->number)
                ->with('error', "Pesanan #{$order->number} berhasil dibuat, tapi gagal membuka halaman pembayaran. Coba lagi dari halaman ini.");
        }

        return Inertia::location($payment->checkout_url);
    }

    /**
     * A guest checkout silently creates a real Customer (random, never-shown
     * password; Lupa Password is how they would set one later) and signs them
     * in, so payment, tracking and order history all treat the order like any
     * other. The guest's session cart is carried over.
     *
     * @param  array<string, mixed>  $validated
     */
    private function registerGuest(Request $request, CartManager $cart, array $validated): Customer
    {
        $customer = Customer::create([
            'code' => CodeSequence::next(Customer::withTrashed(), 'code', 'CUS-'),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(40)),
            'status' => 'aktif',
            'consent_at' => now(),
            'consent_version' => AuthController::CONSENT_VERSION,
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $cart->mergeGuestIntoCustomer($customer);

        $customer->sendEmailVerificationNotification();

        return $customer;
    }

    /**
     * The branches an order could be collected from.
     *
     * @return list<array<string, mixed>>
     */
    private function pickupBranches(Request $request, ?CustomerAddress $address): array
    {
        // A location the shopper shared wins; otherwise the delivery address
        // (saved, or just typed) is where the distance is measured from.
        $coordinates = LocationPreference::coordinates($request)
            ?? ($address?->latitude !== null && $address?->longitude !== null
                ? ['lat' => (float) $address->latitude, 'lng' => (float) $address->longitude]
                : null);

        $query = Branch::query()
            ->active()
            ->where('supports_pickup', true);

        if ($coordinates) {
            $query->withDistanceFrom($coordinates['lat'], $coordinates['lng']);
        }

        return StorefrontBranchPresenter::forPickup($query->get());
    }

    /**
     * @throws ValidationException
     */
    private function pickupBranchFor(string $code): Branch
    {
        $branch = Branch::active()->where('supports_pickup', true)->where('code', $code)->first();

        if (! $branch) {
            throw ValidationException::withMessages(['pickupBranchId' => 'Lokasi pengambilan tidak tersedia. Pilih lokasi lain.']);
        }

        return $branch;
    }

    /**
     * @param  array{branch: Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}  $data
     * @param  array{fulfilment: string, paymentMethod: string, note: ?string, courierQuote?: array{courierCompany: string, courierType: string, courierName: string, serviceName: string, price: int}}  $validated
     */
    private function placeOrder(Customer $customer, array $data, Branch $branch, array $validated): Order
    {
        $coupon = $data['coupon'] ? Coupon::whereKey($data['coupon']->id)->lockForUpdate()->first() : null;

        // Locked, so two orders racing for a coupon's last use cannot both get it.
        if ($coupon?->is_exhausted) {
            throw ValidationException::withMessages(['code' => 'Kuota kode promo sudah habis.']);
        }

        $subtotal = CartManager::subtotal($branch, $data['lines']);
        $discount = $coupon ? $coupon->discountFor($subtotal) : 0;
        $freeShipping = $coupon?->is_free_shipping ?? false;
        $shipping = $validated['fulfilment'] === 'antar' && ! $freeShipping ? $validated['courierQuote']['price'] : 0;
        $tax = (int) round(($subtotal - $discount) * self::TAX_RATE);
        $grandTotal = max($subtotal - $discount + $shipping + $tax, 0);

        $address = $data['address'];
        $note = trim($validated['note'] ?? '');

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

        if ($validated['fulfilment'] === 'antar') {
            $order->shipment()->create([
                'courier_company' => $validated['courierQuote']['courierCompany'],
                'courier_type' => $validated['courierQuote']['courierType'],
                'courier_name' => $validated['courierQuote']['courierName'],
                'courier_service_name' => $validated['courierQuote']['serviceName'],
                'price' => $validated['courierQuote']['price'],
            ]);
        }

        $allocator = new StockAllocator;

        foreach ($data['lines'] as $line) {
            $product = $line['product'];
            $stock = $product->stockAt($branch);
            $price = $stock?->effective_price ?? $product->price;

            try {
                $manifest = $allocator->consume($branch, $product, $line['quantity'], 'penjualan', $order);
            } catch (InsufficientStockException $exception) {
                if ($validated['fulfilment'] === 'ambil') {
                    // Pickup is open to every branch regardless of stock; the
                    // company covers the gap, so nothing is deducted here.
                    $manifest = [];
                } else {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok {$product->name} baru saja berubah: tersisa {$exception->available}, diminta {$exception->requested}.",
                    ]);
                }
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

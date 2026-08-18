<?php

namespace App\Support\Cart;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * The single place that knows how a shopper's cart is stored: a real `Cart`
 * row (+ `cart_items`) for a signed-in customer, a plain session array for a
 * guest — see the `carts` migration's docblock for why. Every controller
 * reads or writes a cart through this class, never the model or the session
 * key directly.
 *
 * A guest's cart only ever needs a branch and a list of products — address,
 * coupon and payment method are picked during checkout, and checkout
 * requires signing in (Fase 0's "boleh checkout sebagai tamu?" is still an
 * open decision; requiring an account sidesteps it without foreclosing
 * either answer later). `applyCoupon()`, `removeCoupon()`, `setAddress()` and
 * `clear()` therefore only operate on a signed-in customer's cart.
 */
class CartManager
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(private readonly Request $request) {}

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    public function current(): array
    {
        return $this->customer()
            ? $this->fromCart($this->cartModel())
            : $this->fromSession();
    }

    public function count(): int
    {
        return (int) collect($this->current()['lines'])->sum('quantity');
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     *
     * @throws CartBranchConflictException
     * @throws ValidationException
     */
    public function addItem(Product $product, Branch $branch, int $quantity, bool $switchBranch = false): array
    {
        $quantity = max($quantity, 1);

        if ($this->customer()) {
            return DB::transaction(function () use ($product, $branch, $quantity, $switchBranch) {
                $cart = $this->cartModel();
                $hasItems = $cart->items()->exists();

                if ($cart->branch_id && $cart->branch_id !== $branch->id && $hasItems) {
                    if (! $switchBranch) {
                        throw new CartBranchConflictException($cart->branch);
                    }

                    $cart->items()->delete();
                    $cart->update(['coupon_id' => null, 'customer_address_id' => null]);
                }

                $cart->update(['branch_id' => $branch->id]);

                $existing = $cart->items()->where('product_id', $product->id)->first();
                $total = ($existing?->quantity ?? 0) + $quantity;

                $this->assertPurchasable($product, $branch, $total);

                $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $total]);

                return $this->fromCart($cart);
            });
        }

        $session = $this->sessionCart();
        $hasItems = count($session['items']) > 0;

        if ($session['branch_id'] && $session['branch_id'] !== $branch->id && $hasItems) {
            if (! $switchBranch) {
                throw new CartBranchConflictException(Branch::findOrFail($session['branch_id']));
            }

            $session['items'] = [];
        }

        $session['branch_id'] = $branch->id;
        $total = ($session['items'][$product->id] ?? 0) + $quantity;

        $this->assertPurchasable($product, $branch, $total);

        $session['items'][$product->id] = $total;
        $this->putSession($session);

        return $this->fromSession();
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    public function updateItem(Product $product, int $quantity): array
    {
        if ($quantity < 1) {
            return $this->removeItem($product);
        }

        if ($this->customer()) {
            $cart = $this->cartModel();
            $branch = $cart->branch;

            if ($branch) {
                $this->assertPurchasable($product, $branch, $quantity);
            }

            $cart->items()->where('product_id', $product->id)->update(['quantity' => $quantity]);

            return $this->fromCart($cart);
        }

        $session = $this->sessionCart();

        if (! array_key_exists($product->id, $session['items'])) {
            return $this->fromSession();
        }

        if ($session['branch_id']) {
            $this->assertPurchasable($product, Branch::findOrFail($session['branch_id']), $quantity);
        }

        $session['items'][$product->id] = $quantity;
        $this->putSession($session);

        return $this->fromSession();
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    public function removeItem(Product $product): array
    {
        if ($this->customer()) {
            $cart = $this->cartModel();
            $cart->items()->where('product_id', $product->id)->delete();

            if (! $cart->items()->exists()) {
                $cart->update(['branch_id' => null, 'coupon_id' => null, 'customer_address_id' => null]);
            }

            return $this->fromCart($cart);
        }

        $session = $this->sessionCart();
        unset($session['items'][$product->id]);

        if (count($session['items']) === 0) {
            $session['branch_id'] = null;
        }

        $this->putSession($session);

        return $this->fromSession();
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    public function setAddress(CustomerAddress $address): array
    {
        $cart = $this->cartModel();
        $cart->update(['customer_address_id' => $address->id]);

        return $this->fromCart($cart);
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     *
     * @throws ValidationException
     */
    public function applyCoupon(string $code): array
    {
        $cart = $this->cartModel();
        $data = $this->fromCart($cart);

        if (! $data['branch']) {
            throw ValidationException::withMessages(['code' => 'Keranjang Anda masih kosong.']);
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        $this->assertCouponUsable($coupon, $data['branch'], self::subtotal($data['branch'], $data['lines']));

        $cart->update(['coupon_id' => $coupon->id]);

        return $this->fromCart($cart);
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    public function removeCoupon(): array
    {
        $cart = $this->cartModel();
        $cart->update(['coupon_id' => null]);

        return $this->fromCart($cart);
    }

    public function clear(): void
    {
        if ($this->customer()) {
            $cart = $this->cartModel();
            $cart->items()->delete();
            $cart->update([
                'branch_id' => null, 'coupon_id' => null,
                'customer_address_id' => null, 'fulfilment' => null, 'payment_method' => null,
            ]);

            return;
        }

        $this->request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Folds a just-signed-in guest's session cart into their real cart. If the
     * customer already had items from a different branch in their saved cart,
     * the saved cart wins and the guest items are discarded — a login should
     * never silently blow away a cart the customer built while signed in on
     * another device.
     */
    public function mergeGuestIntoCustomer(Customer $customer): void
    {
        $session = $this->sessionCart();

        if (count($session['items']) === 0) {
            $this->request->session()->forget(self::SESSION_KEY);

            return;
        }

        $cart = Cart::firstOrCreate(['customer_id' => $customer->id]);
        $hasItems = $cart->items()->exists();

        if ($cart->branch_id && $session['branch_id'] && $cart->branch_id !== $session['branch_id'] && $hasItems) {
            $this->request->session()->forget(self::SESSION_KEY);

            return;
        }

        $cart->update(['branch_id' => $cart->branch_id ?? $session['branch_id']]);
        $branch = $cart->branch;

        foreach ($session['items'] as $productId => $quantity) {
            $product = Product::find($productId);

            if (! $product || ! $branch) {
                continue;
            }

            $existing = $cart->items()->where('product_id', $product->id)->first();
            $total = ($existing?->quantity ?? 0) + $quantity;

            // A merge should never fail login outright — cap silently against
            // what is actually purchasable instead of throwing.
            $stock = $product->stockAt($branch);
            $available = $stock ? max($stock->quantity - $stock->reserved_quantity, 0) : 0;
            $cap = $product->max_qty_per_order ? min($available, $product->max_qty_per_order) : $available;
            $total = min($total, max($cap, 0));

            if ($total > 0) {
                $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $total]);
            }
        }

        $this->request->session()->forget(self::SESSION_KEY);
    }

    private function customer(): ?Customer
    {
        return $this->request->user('customer');
    }

    private function cartModel(): Cart
    {
        $customer = $this->customer() ?? throw new LogicException('CartManager: no signed-in customer.');

        return Cart::with(['items.product', 'branch', 'address', 'coupon'])
            ->firstOrCreate(['customer_id' => $customer->id]);
    }

    /**
     * @return array{branch_id: ?int, items: array<int, int>}
     */
    private function sessionCart(): array
    {
        return $this->request->session()->get(self::SESSION_KEY, ['branch_id' => null, 'items' => []]);
    }

    /**
     * @param  array{branch_id: ?int, items: array<int, int>}  $session
     */
    private function putSession(array $session): void
    {
        $this->request->session()->put(self::SESSION_KEY, $session);
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    private function fromCart(Cart $cart): array
    {
        $cart->loadMissing(['items.product', 'branch', 'address', 'coupon']);

        return [
            'branch' => $cart->branch,
            'address' => $cart->address,
            'coupon' => $cart->coupon,
            'lines' => $cart->items->map(fn ($item) => [
                'product' => $item->product,
                'quantity' => $item->quantity,
            ])->all(),
        ];
    }

    /**
     * @return array{branch: ?Branch, address: ?CustomerAddress, coupon: ?Coupon, lines: list<array{product: Product, quantity: int}>}
     */
    private function fromSession(): array
    {
        $session = $this->sessionCart();
        $branch = $session['branch_id'] ? Branch::find($session['branch_id']) : null;

        $products = Product::whereIn('id', array_keys($session['items']))->get()->keyBy('id');

        return [
            'branch' => $branch,
            'address' => null,
            'coupon' => null,
            'lines' => collect($session['items'])
                ->filter(fn ($quantity, $productId) => $products->has($productId))
                ->map(fn ($quantity, $productId) => ['product' => $products[$productId], 'quantity' => $quantity])
                ->values()
                ->all(),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function assertPurchasable(Product $product, Branch $branch, int $totalRequested): void
    {
        if ($product->max_qty_per_order && $totalRequested > $product->max_qty_per_order) {
            throw ValidationException::withMessages([
                'quantity' => "Maksimal pembelian {$product->name} adalah {$product->max_qty_per_order} per transaksi.",
            ]);
        }

        $stock = $product->stockAt($branch);
        $available = $stock ? max($stock->quantity - $stock->reserved_quantity, 0) : 0;

        if ($totalRequested > $available) {
            throw ValidationException::withMessages([
                'quantity' => "Stok {$product->name} di {$branch->name} hanya tersisa {$available}.",
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertCouponUsable(?Coupon $coupon, Branch $branch, int $subtotal): void
    {
        if (! $coupon || $coupon->status !== 'aktif') {
            throw ValidationException::withMessages(['code' => 'Kode promo tidak ditemukan.']);
        }

        if ($coupon->is_expired) {
            throw ValidationException::withMessages(['code' => 'Kode promo sudah kedaluwarsa.']);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['code' => 'Kode promo belum berlaku.']);
        }

        if ($coupon->is_exhausted) {
            throw ValidationException::withMessages(['code' => 'Kuota kode promo sudah habis.']);
        }

        if (! $coupon->appliesToBranch($branch)) {
            throw ValidationException::withMessages(['code' => "Kode promo ini tidak berlaku di {$branch->name}."]);
        }

        if ($coupon->minimum_purchase && $subtotal < $coupon->minimum_purchase) {
            throw ValidationException::withMessages([
                'code' => 'Belanja minimum untuk kode promo ini belum tercapai.',
            ]);
        }

        $customer = $this->customer();

        if ($customer && Order::withoutGlobalScopes()
            ->where('coupon_id', $coupon->id)
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', ['dibatalkan', 'kedaluwarsa'])
            ->exists()
        ) {
            throw ValidationException::withMessages(['code' => 'Anda sudah pernah memakai kode promo ini.']);
        }
    }

    /**
     * @param  list<array{product: Product, quantity: int}>  $lines
     */
    public static function subtotal(Branch $branch, array $lines): int
    {
        return (int) collect($lines)->sum(function (array $line) use ($branch) {
            $stock = $line['product']->stockAt($branch);
            $price = $stock?->effective_price ?? $line['product']->price;

            return $price * $line['quantity'];
        });
    }
}

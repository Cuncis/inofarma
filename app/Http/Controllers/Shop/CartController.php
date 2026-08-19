<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Support\Cart\CartBranchConflictException;
use App\Support\Cart\CartManager;
use App\Support\Presenters\CartPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shopping cart — the one part of checkout a guest can use before signing
 * in (see `CartManager`'s docblock). Coupon application is the exception:
 * it requires a signed-in customer, gated by the `customer` middleware on
 * those two routes.
 */
class CartController extends Controller
{
    public function index(CartManager $cart): Response|RedirectResponse
    {
        $data = $cart->current();

        // Checked here rather than left to the page's own client-side effect
        // — that redirect always painted the (empty-looking) cart for one
        // frame first, an avoidable flash. Deciding before render never does.
        if (count($data['lines']) === 0) {
            return redirect()->route('ui.cart-empty');
        }

        return Inertia::render('Shop/Cart', [
            'cart' => CartPresenter::toArray($data),
        ]);
    }

    public function store(Request $request, CartManager $cart): RedirectResponse
    {
        $data = $request->validate([
            'productId' => ['required', 'string'],
            'branchId' => ['required', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'switchBranch' => ['nullable', 'boolean'],
        ]);

        $product = $this->findProduct($data['productId']);
        $branch = $this->findBranch($data['branchId']);

        try {
            $cart->addItem($product, $branch, (int) ($data['quantity'] ?? 1), (bool) ($data['switchBranch'] ?? false));
        } catch (CartBranchConflictException $exception) {
            throw ValidationException::withMessages([
                'branch' => "{$exception->getMessage()} Kosongkan keranjang dan pindah ke {$branch->name}?",
            ]);
        }

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, string $product, CartManager $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $cart->updateItem($this->findProduct($product), (int) $data['quantity']);

        return back();
    }

    public function destroy(string $product, CartManager $cart): RedirectResponse
    {
        $cart->removeItem($this->findProduct($product));

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function applyCoupon(Request $request, CartManager $cart): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $cart->applyCoupon($data['code']);

        return back()->with('success', 'Kode promo berhasil dipakai.');
    }

    public function removeCoupon(CartManager $cart): RedirectResponse
    {
        $cart->removeCoupon();

        return back();
    }

    private function findProduct(string $sku): Product
    {
        return Product::where('sku', $sku)->where('status', 'aktif')
            ->firstOr(fn () => abort(404, 'Produk tidak ditemukan.'));
    }

    private function findBranch(string $code): Branch
    {
        return Branch::where('code', $code)
            ->firstOr(fn () => abort(404, 'Cabang tidak ditemukan.'));
    }
}

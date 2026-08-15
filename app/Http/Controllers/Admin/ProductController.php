<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Support\Catalog;
use App\Support\CategoryStore;
use App\Support\ProductStore;
use App\Support\SellerStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Product CRUD for the admin.
 *
 * Backed by `ProductStore` (session), not a database — the shape of the actions
 * is the same one an Eloquent-backed controller would have, so wiring a real
 * table later is a change of store, not of flow.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductStore $products,
        private readonly CategoryStore $categories,
        private readonly SellerStore $sellers,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/ProductList', [
            'products' => $this->products->all(),
            'categories' => $this->categories->names(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ProductAdd', $this->formOptions());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->validated());

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$product['name']}\" berhasil ditambahkan.");
    }

    public function show(string $product): Response
    {
        return Inertia::render('Admin/ProductDetail', [
            'product' => $this->products->findOrFail($product),
        ]);
    }

    public function edit(string $product): Response
    {
        return Inertia::render('Admin/ProductEdit', [
            ...$this->formOptions(),
            'product' => $this->products->findOrFail($product),
        ]);
    }

    public function update(ProductRequest $request, string $product): RedirectResponse
    {
        $updated = $this->products->update($product, $request->validated());

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$updated['name']}\" berhasil diperbarui.");
    }

    public function destroy(string $product): RedirectResponse
    {
        $target = $this->products->findOrFail($product);
        $this->products->delete($product);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$target['name']}\" berhasil dihapus.");
    }

    /**
     * Restore the seed catalogue — the prototype's way back from a mess.
     */
    public function reset(): RedirectResponse
    {
        $this->products->reset();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Katalog dikembalikan ke data awal.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function formOptions(): array
    {
        return [
            'categories' => $this->categories->names(),
            'sellers' => $this->sellers->names(),
            'units' => Catalog::units(),
            'statuses' => Catalog::statuses(),
        ];
    }
}

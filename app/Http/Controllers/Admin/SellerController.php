<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRequest;
use App\Support\Catalog;
use App\Support\SellerStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Seller CRUD for the admin.
 *
 * Products name their seller, so renaming one cascades and deleting a seller
 * who still stocks products is refused.
 */
class SellerController extends Controller
{
    public function __construct(private readonly SellerStore $sellers) {}

    public function index(): Response
    {
        return Inertia::render('Admin/SellerList', [
            'sellers' => $this->sellers->withStats(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SellerAdd', [
            'statuses' => Catalog::sellerStatuses(),
        ]);
    }

    public function store(SellerRequest $request): RedirectResponse
    {
        $seller = $this->sellers->create($request->validated());

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Penjual \"{$seller['name']}\" berhasil ditambahkan.");
    }

    public function show(string $seller): Response
    {
        $record = $this->sellers->findOrFail($seller);

        return Inertia::render('Admin/SellerDetail', [
            'seller' => $record,
            'products' => $this->sellers->productsFor($record['name']),
        ]);
    }

    public function edit(string $seller): Response
    {
        return Inertia::render('Admin/SellerEdit', [
            'seller' => $this->sellers->findOrFail($seller),
            'statuses' => Catalog::sellerStatuses(),
        ]);
    }

    public function update(SellerRequest $request, string $seller): RedirectResponse
    {
        $updated = $this->sellers->update($seller, $request->validated());

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Data \"{$updated['name']}\" berhasil diperbarui.");
    }

    public function destroy(string $seller): RedirectResponse
    {
        $record = $this->sellers->findOrFail($seller);

        if (! $this->sellers->delete($seller)) {
            return redirect()
                ->route('admin.penjual.index')
                ->with('error', "\"{$record['name']}\" masih menjual {$record['products']} produk dan tidak bisa dihapus.");
        }

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Penjual \"{$record['name']}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        $this->sellers->reset();

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', 'Daftar penjual dikembalikan ke data awal.');
    }
}

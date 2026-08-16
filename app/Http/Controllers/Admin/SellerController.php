<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\AdminOptions;
use App\Support\CodeSequence;
use App\Support\Presenters\ProductPresenter;
use App\Support\Presenters\SupplierPresenter;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Supplier CRUD, shown to the admin as "Penjual".
 *
 * Renaming no longer cascades: products hold a foreign key, so the new name is
 * simply what the relation reads back. A supplier that still has products
 * cannot be deleted — `products.supplier_id` is `restrictOnDelete`.
 */
class SellerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/SellerList', [
            'sellers' => SupplierPresenter::collection($this->withStats()->orderBy('id')->get()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SellerAdd', [
            'statuses' => AdminOptions::labels(AdminOptions::SUPPLIER_STATUSES),
        ]);
    }

    public function store(SellerRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($this->attributes($request->validated()) + [
            'code' => CodeSequence::next(Supplier::withTrashed(), 'code', 'SEL-'),
        ]);

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Penjual \"{$supplier->name}\" berhasil ditambahkan.");
    }

    public function show(string $seller): Response
    {
        $record = $this->find($seller);

        $products = Product::query()
            ->with(['category', 'supplier', 'images'])
            ->withSum('stocks', 'quantity')
            ->where('supplier_id', $record->id)
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/SellerDetail', [
            'seller' => SupplierPresenter::toArray($record),
            'products' => ProductPresenter::collection($products),
        ]);
    }

    public function edit(string $seller): Response
    {
        return Inertia::render('Admin/SellerEdit', [
            'seller' => SupplierPresenter::toArray($this->find($seller)),
            'statuses' => AdminOptions::labels(AdminOptions::SUPPLIER_STATUSES),
        ]);
    }

    public function update(SellerRequest $request, string $seller): RedirectResponse
    {
        $record = $this->find($seller);
        $record->update($this->attributes($request->validated()));

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Data \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $seller): RedirectResponse
    {
        $record = $this->find($seller);
        $count = $record->products()->count();

        if ($count > 0) {
            return redirect()
                ->route('admin.penjual.index')
                ->with('error', "\"{$record->name}\" masih menjual {$count} produk dan tidak bisa dihapus.");
        }

        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Penjual \"{$name}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', 'Daftar penjual dikembalikan ke data awal.');
    }

    /**
     * Product count and revenue as subqueries, so the list is a single query
     * rather than one per supplier.
     *
     * @return Builder<Supplier>
     */
    private function withStats(): Builder
    {
        return Supplier::query()
            ->withCount('products')
            ->addSelect(['revenue' => Product::query()
                ->selectRaw('coalesce(sum(price * sold_count), 0)')
                ->whereColumn('supplier_id', 'suppliers.id')
                ->whereNull('deleted_at'),
            ]);
    }

    private function find(string $code): Supplier
    {
        return $this->withStats()
            ->where('code', $code)
            ->firstOr(fn () => abort(404, 'Penjual tidak ditemukan.'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'contact_person' => $data['owner'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'license_number' => $data['license'],
            'kota' => $data['city'],
            'address_line' => $data['address'] ?? null,
            'status' => AdminOptions::toValue(AdminOptions::SUPPLIER_STATUSES, $data['status']),
        ];
    }
}

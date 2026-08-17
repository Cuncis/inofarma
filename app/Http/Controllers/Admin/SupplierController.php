<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
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
 * Supplier CRUD, shown to the admin as "Pemasok" (Fase 4.3, utang teknis #5 —
 * this used to read "Penjual", a marketplace-seller word that never fit a
 * chain sourcing its own stock).
 *
 * Renaming no longer cascades: products hold a foreign key, so the new name is
 * simply what the relation reads back. A supplier that still has products
 * cannot be deleted — `products.supplier_id` is `restrictOnDelete`.
 */
class SupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/SupplierList', [
            'suppliers' => SupplierPresenter::collection($this->withStats()->orderBy('id')->get()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SupplierAdd', [
            'statuses' => AdminOptions::labels(AdminOptions::SUPPLIER_STATUSES),
        ]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($this->attributes($request->validated()) + [
            'code' => CodeSequence::next(Supplier::withTrashed(), 'code', 'SEL-'),
        ]);

        return redirect()
            ->route('admin.pemasok.index')
            ->with('success', "Pemasok \"{$supplier->name}\" berhasil ditambahkan.");
    }

    public function show(string $supplier): Response
    {
        $record = $this->find($supplier);

        $products = Product::query()
            ->with(['category', 'supplier', 'images'])
            ->withSum('stocks', 'quantity')
            ->where('supplier_id', $record->id)
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/SupplierDetail', [
            'supplier' => SupplierPresenter::toArray($record),
            'products' => ProductPresenter::collection($products),
        ]);
    }

    public function edit(string $supplier): Response
    {
        return Inertia::render('Admin/SupplierEdit', [
            'supplier' => SupplierPresenter::toArray($this->find($supplier)),
            'statuses' => AdminOptions::labels(AdminOptions::SUPPLIER_STATUSES),
        ]);
    }

    public function update(SupplierRequest $request, string $supplier): RedirectResponse
    {
        $record = $this->find($supplier);
        $record->update($this->attributes($request->validated()));

        return redirect()
            ->route('admin.pemasok.index')
            ->with('success', "Data \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $supplier): RedirectResponse
    {
        $record = $this->find($supplier);
        $count = $record->products()->count();

        if ($count > 0) {
            return redirect()
                ->route('admin.pemasok.index')
                ->with('error', "\"{$record->name}\" masih memasok {$count} produk dan tidak bisa dihapus.");
        }

        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.pemasok.index')
            ->with('success', "Pemasok \"{$name}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.pemasok.index')
            ->with('success', 'Daftar pemasok dikembalikan ke data awal.');
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
            ->firstOr(fn () => abort(404, 'Pemasok tidak ditemukan.'));
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\AdminOptions;
use App\Support\CodeSequence;
use App\Support\Presenters\ProductPresenter;
use App\Support\Slug;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Product CRUD for the admin.
 *
 * Products are addressed by SKU, not by primary key, so `/admin/produk/PRD-001`
 * stays meaningful and survives a reseed.
 */
class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['category', 'supplier', 'images'])
            ->withSum('stocks', 'quantity')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/ProductList', [
            'products' => ProductPresenter::collection($products),
            'categories' => Category::orderBy('position')->pluck('name')->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ProductAdd', $this->formOptions());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($this->attributes($request->validated()) + [
            'sku' => CodeSequence::next(Product::withTrashed(), 'sku', 'PRD-'),
        ]);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$product->name}\" berhasil ditambahkan.");
    }

    public function show(string $product): Response
    {
        $record = $this->find($product, ['stocks.branch']);

        return Inertia::render('Admin/ProductDetail', [
            'product' => ProductPresenter::withBranches($record),
        ]);
    }

    public function edit(string $product): Response
    {
        return Inertia::render('Admin/ProductEdit', [
            ...$this->formOptions(),
            'product' => ProductPresenter::toArray($this->find($product)),
        ]);
    }

    public function update(ProductRequest $request, string $product): RedirectResponse
    {
        $record = $this->find($product);
        $record->update($this->attributes($request->validated(), $record));

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $product): RedirectResponse
    {
        $record = $this->find($product);
        $name = $record->name;

        // Soft delete: order history points at this row, and an old order has to
        // keep reading back the way it was placed.
        $record->delete();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', "Produk \"{$name}\" berhasil dihapus.");
    }

    /**
     * Restore the demo catalogue — a development affordance, not a production one.
     */
    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Katalog dikembalikan ke data awal.');
    }

    /**
     * @param  list<string>  $with
     */
    private function find(string $sku, array $with = []): Product
    {
        return Product::query()
            ->with(['category', 'supplier', 'images', ...$with])
            ->withSum('stocks', 'quantity')
            ->where('sku', $sku)
            ->firstOr(fn () => abort(404, 'Produk tidak ditemukan.'));
    }

    /**
     * Translate the form's names and labels into columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, ?Product $editing = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => Slug::unique(Product::withTrashed(), $data['name'], 'slug', $editing?->id),
            'category_id' => Category::where('name', $data['category'])->value('id'),
            'supplier_id' => Supplier::where('name', $data['seller'])->value('id'),
            'unit' => $data['unit'],
            'status' => AdminOptions::toValue(AdminOptions::PRODUCT_STATUSES, $data['status']),
            'price' => $data['price'],
            'old_price' => $data['oldPrice'] ?? null,
            'requires_prescription' => $data['prescription'],
            'blurb' => $data['blurb'] ?? null,

            // --- Data farmasi (Fase 4.2) ---
            'drug_class' => AdminOptions::toValue(AdminOptions::DRUG_CLASSES, $data['drugClass'] ?? 'Non-Obat'),
            'nie_bpom' => $data['nie'] ?? null,
            'composition' => $data['composition'] ?? null,
            'indication' => $data['indication'] ?? null,
            'dosage' => $data['dosage'] ?? null,
            'side_effects' => $data['sideEffects'] ?? null,
            'warning' => $data['warning'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'max_qty_per_order' => $data['maxQtyPerOrder'] ?? null,
            'storage' => AdminOptions::toValue(AdminOptions::STORAGE_CONDITIONS, $data['storage'] ?? 'Suhu Ruang'),
            'weight_grams' => $data['weightGrams'] ?? 0,
            'length_cm' => $data['lengthCm'] ?? 0,
            'width_cm' => $data['widthCm'] ?? 0,
            'height_cm' => $data['heightCm'] ?? 0,
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('position')->pluck('name')->all(),
            'sellers' => Supplier::orderBy('id')->pluck('name')->all(),
            'units' => AdminOptions::units(),
            'statuses' => AdminOptions::labels(AdminOptions::PRODUCT_STATUSES),
            'drugClasses' => AdminOptions::labels(AdminOptions::DRUG_CLASSES),
            'storageConditions' => AdminOptions::labels(AdminOptions::STORAGE_CONDITIONS),
        ];
    }
}

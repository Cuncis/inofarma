<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Support\Catalog;
use App\Support\CategoryStore;
use App\Support\ProductStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Category CRUD for the admin.
 *
 * Backed by `CategoryStore` (session) rather than a database. Deleting is
 * refused while products still reference the category, so the catalogue can
 * never end up pointing at a category that no longer exists.
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryStore $categories,
        private readonly ProductStore $products,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/CategoryList', [
            'categories' => $this->categories->withCounts(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CategoryAdd', [
            'statuses' => Catalog::categoryStatuses(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = $this->categories->create($request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$category['name']}\" berhasil ditambahkan.");
    }

    public function show(string $category): Response
    {
        $record = $this->categories->findOrFail($category);

        return Inertia::render('Admin/CategoryDetail', [
            'category' => $record,
            'products' => array_values(array_filter(
                $this->products->all(),
                fn (array $product) => $product['category'] === $record['name'],
            )),
        ]);
    }

    public function edit(string $category): Response
    {
        return Inertia::render('Admin/CategoryEdit', [
            'category' => $this->categories->findOrFail($category),
            'statuses' => Catalog::categoryStatuses(),
        ]);
    }

    public function update(CategoryRequest $request, string $category): RedirectResponse
    {
        $updated = $this->categories->update($category, $request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$updated['name']}\" berhasil diperbarui.");
    }

    public function destroy(string $category): RedirectResponse
    {
        $record = $this->categories->findOrFail($category);
        $count = $this->categories->productCount($record['name']);

        if (! $this->categories->delete($category)) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori \"{$record['name']}\" masih dipakai {$count} produk dan tidak bisa dihapus.");
        }

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$record['name']}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        $this->categories->reset();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Daftar kategori dikembalikan ke data awal.');
    }
}

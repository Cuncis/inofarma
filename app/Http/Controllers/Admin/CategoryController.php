<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\Presenters\CategoryPresenter;
use App\Support\Presenters\ProductPresenter;
use App\Support\Slug;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Category CRUD for the admin.
 *
 * Products point at a category by foreign key with `restrictOnDelete`, so a
 * category still in use cannot be removed. The check below is there to give a
 * readable message; the database enforces it either way.
 *
 * Renaming needs no cascade any more — products read the name through the
 * relation, so it follows automatically.
 */
class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/CategoryList', [
            'categories' => CategoryPresenter::collection($categories),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CategoryAdd', [
            'statuses' => AdminOptions::labels(AdminOptions::CATEGORY_STATUSES),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Slug::unique(Category::withTrashed(), $data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'status' => AdminOptions::toValue(AdminOptions::CATEGORY_STATUSES, $data['status']),
            'position' => (int) Category::max('position') + 1,
            'image_path' => '/media/images/small/img-'.(Category::count() % 12 + 1).'.jpg',
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$category->name}\" berhasil ditambahkan.");
    }

    public function show(string $category): Response
    {
        $record = $this->find($category);

        $products = Product::query()
            ->with(['category', 'supplier', 'images'])
            ->withSum('stocks', 'quantity')
            ->where('category_id', $record->id)
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/CategoryDetail', [
            'category' => CategoryPresenter::toArray($record),
            'products' => ProductPresenter::collection($products),
        ]);
    }

    public function edit(string $category): Response
    {
        return Inertia::render('Admin/CategoryEdit', [
            'category' => CategoryPresenter::toArray($this->find($category)),
            'statuses' => AdminOptions::labels(AdminOptions::CATEGORY_STATUSES),
        ]);
    }

    public function update(CategoryRequest $request, string $category): RedirectResponse
    {
        $record = $this->find($category);
        $data = $request->validated();

        $record->update([
            'name' => $data['name'],
            'slug' => Slug::unique(
                Category::withTrashed(),
                $data['slug'] ?? $record->slug,
                'slug',
                $record->id,
            ),
            'description' => $data['description'] ?? null,
            'status' => AdminOptions::toValue(AdminOptions::CATEGORY_STATUSES, $data['status']),
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $category): RedirectResponse
    {
        $record = $this->find($category);
        $count = $record->products()->count();

        if ($count > 0) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori \"{$record->name}\" masih dipakai {$count} produk dan tidak bisa dihapus.");
        }

        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Daftar kategori dikembalikan ke data awal.');
    }

    private function find(string $slug): Category
    {
        return Category::where('slug', $slug)
            ->firstOr(fn () => abort(404, 'Kategori tidak ditemukan.'));
    }
}

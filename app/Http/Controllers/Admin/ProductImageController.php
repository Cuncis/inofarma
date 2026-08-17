<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ProductImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Product photo upload, reordering, primary selection and removal.
 *
 * Kept separate from `ProductController` because these are multipart requests
 * against a product that already exists — a product's own attributes save as
 * JSON through `ProductRequest`, and mixing the two would force every product
 * save through `forceFormData`.
 */
class ProductImageController extends Controller
{
    public function store(Request $request, string $product): RedirectResponse
    {
        $record = $this->find($product);

        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ], [], ['images' => 'gambar']);

        $position = (int) $record->images()->max('position') + 1;
        $hasPrimary = $record->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $file) {
            $upload = ProductImageUploader::store($file, $record->id);

            $record->images()->create([
                'path' => $upload['path'],
                'position' => $position++,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }

        return back()->with('success', 'Gambar berhasil diunggah.');
    }

    public function destroy(string $product, int $image): RedirectResponse
    {
        $record = $this->find($product);
        $target = $record->images()->findOrFail($image);
        $wasPrimary = $target->is_primary;

        if (! str_starts_with($target->path, '/media/')) {
            ProductImageUploader::destroy($target->path);
        }

        $target->delete();

        if ($wasPrimary) {
            $record->images()->orderBy('position')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function makePrimary(string $product, int $image): RedirectResponse
    {
        $record = $this->find($product);
        $record->images()->update(['is_primary' => false]);
        $record->images()->findOrFail($image)->update(['is_primary' => true]);

        return back()->with('success', 'Gambar utama diperbarui.');
    }

    public function reorder(Request $request, string $product): RedirectResponse
    {
        $record = $this->find($product);

        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct', Rule::in($record->images()->pluck('id')->all())],
        ]);

        foreach ($data['order'] as $position => $imageId) {
            $record->images()->where('id', $imageId)->update(['position' => $position]);
        }

        return back()->with('success', 'Urutan gambar diperbarui.');
    }

    private function find(string $sku): Product
    {
        return Product::where('sku', $sku)->firstOr(fn () => abort(404, 'Produk tidak ditemukan.'));
    }
}

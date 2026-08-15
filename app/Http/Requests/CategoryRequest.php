<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use App\Support\CategoryStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $store = app(CategoryStore::class);
        $editing = $this->route('category');

        // A name has to stay unique, but the record being edited keeps its own.
        $taken = collect($store->all())
            ->reject(fn (array $category) => $category['id'] === $editing)
            ->pluck('name')
            ->all();

        return [
            'name' => ['required', 'string', 'max:80', Rule::notIn($taken)],
            'slug' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'status' => ['required', Rule::in(Catalog::categoryStatuses())],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kategori',
            'slug' => 'slug',
            'status' => 'status',
            'description' => 'deskripsi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.not_in' => 'Kategori dengan nama ini sudah ada.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // A name has to stay unique, but the record being edited keeps its own.
        $editing = Category::where('slug', $this->route('category'))->value('id');

        return [
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique(Category::class, 'name')->ignore($editing)->whereNull('deleted_at'),
            ],
            'slug' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::CATEGORY_STATUSES))],
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
            'name.unique' => 'Kategori dengan nama ini sudah ada.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
        ];
    }
}

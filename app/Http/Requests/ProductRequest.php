<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Supplier;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Category and seller arrive as names rather than ids because that is what the
 * form's dropdowns carry. They are checked against the live tables, so a
 * category created a moment ago is immediately selectable.
 *
 * There is deliberately no `stock` field: stock belongs to a product at a
 * branch, and one number on this form cannot say which of ten branches it means.
 */
class ProductRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::exists(Category::class, 'name')->whereNull('deleted_at')],
            'seller' => ['required', Rule::exists(Supplier::class, 'name')->whereNull('deleted_at')],
            'unit' => ['required', Rule::in(AdminOptions::units())],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::PRODUCT_STATUSES))],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'oldPrice' => ['nullable', 'integer', 'min:0', 'max:1000000000', 'gt:price'],
            'prescription' => ['required', 'boolean'],
            'blurb' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama produk',
            'category' => 'kategori',
            'seller' => 'penjual',
            'unit' => 'satuan',
            'status' => 'status',
            'price' => 'harga jual',
            'oldPrice' => 'harga coret',
            'prescription' => 'status resep',
            'blurb' => 'deskripsi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'oldPrice.gt' => 'Harga coret harus lebih besar dari harga jual.',
            'category.exists' => 'Kategori ini tidak terdaftar.',
            'seller.exists' => 'Penjual ini tidak terdaftar.',
        ];
    }
}

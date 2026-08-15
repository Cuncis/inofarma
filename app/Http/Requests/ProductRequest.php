<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use App\Support\CategoryStore;
use App\Support\SellerStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(app(CategoryStore::class)->names())],
            'seller' => ['required', Rule::in(app(SellerStore::class)->names())],
            'unit' => ['required', Rule::in(Catalog::units())],
            'status' => ['required', Rule::in(Catalog::statuses())],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'oldPrice' => ['nullable', 'integer', 'min:0', 'max:1000000000', 'gt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
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
            'stock' => 'stok',
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
        ];
    }
}

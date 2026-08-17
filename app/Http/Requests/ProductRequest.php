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

            // --- Data farmasi (Fase 4.2) ---
            'drugClass' => ['nullable', Rule::in(AdminOptions::labels(AdminOptions::DRUG_CLASSES))],
            'nie' => ['nullable', 'string', 'max:40'],
            'composition' => ['nullable', 'string', 'max:255'],
            'indication' => ['nullable', 'string', 'max:1000'],
            'dosage' => ['nullable', 'string', 'max:1000'],
            'sideEffects' => ['nullable', 'string', 'max:1000'],
            'warning' => ['nullable', 'string', 'max:1000', 'required_if:drugClass,Bebas Terbatas'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'maxQtyPerOrder' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'storage' => ['nullable', Rule::in(AdminOptions::labels(AdminOptions::STORAGE_CONDITIONS))],
            'weightGrams' => ['nullable', 'integer', 'min:0', 'max:100000'],
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
            'drugClass' => 'golongan obat',
            'nie' => 'nomor izin edar',
            'composition' => 'komposisi',
            'indication' => 'indikasi',
            'dosage' => 'aturan pakai',
            'sideEffects' => 'efek samping',
            'warning' => 'peringatan',
            'manufacturer' => 'produsen',
            'maxQtyPerOrder' => 'batas pembelian',
            'storage' => 'kondisi penyimpanan',
            'weightGrams' => 'berat',
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
            'warning.required_if' => 'Obat bebas terbatas wajib menampilkan peringatan P1–P6.',
        ];
    }
}

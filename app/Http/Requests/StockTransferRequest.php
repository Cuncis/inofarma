<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StockTransferRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'fromBranch' => ['required', Rule::exists(Branch::class, 'code')->whereNull('deleted_at')],
            'toBranch' => ['required', 'different:fromBranch', Rule::exists(Branch::class, 'code')->whereNull('deleted_at')],
            'product' => ['required', Rule::exists(Product::class, 'sku')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fromBranch' => 'cabang asal',
            'toBranch' => 'cabang tujuan',
            'product' => 'produk',
            'quantity' => 'jumlah',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'toBranch.different' => 'Cabang tujuan harus berbeda dari cabang asal.',
            'fromBranch.exists' => 'Cabang asal tidak terdaftar.',
            'toBranch.exists' => 'Cabang tujuan tidak terdaftar.',
            'product.exists' => 'Produk tidak terdaftar.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $stock = BranchStock::whereHas(
                'branch',
                fn ($query) => $query->where('code', $this->input('fromBranch')),
            )->whereHas(
                'product',
                fn ($query) => $query->where('sku', $this->input('product')),
            )->first();

            $available = $stock ? $stock->quantity - $stock->reserved_quantity : 0;

            if ($available < (int) $this->input('quantity')) {
                $validator->errors()->add(
                    'quantity',
                    "Stok di cabang asal hanya tersisa {$available}.",
                );
            }
        });
    }
}

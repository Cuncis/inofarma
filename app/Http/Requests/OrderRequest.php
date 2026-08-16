<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * An order belongs to exactly one branch and is either delivered or collected.
 * Both are required by the schema, because "which shelf does this come off" is
 * not answerable later — it decides which stock gets reserved.
 */
class OrderRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customerEmail' => [
                'required', 'email',
                Rule::exists(Customer::class, 'email')->whereNull('deleted_at'),
            ],
            'branch' => ['required', Rule::exists(Branch::class, 'code')->whereNull('deleted_at')],
            'fulfilment' => ['required', Rule::in(AdminOptions::labels(AdminOptions::FULFILMENTS))],
            'payment' => ['required', Rule::in(AdminOptions::paymentMethods())],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::ORDER_STATUSES))],
            'shipping' => ['required', 'integer', 'min:0', 'max:10000000'],
            'note' => ['nullable', 'string', 'max:500'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => [
                'required',
                Rule::exists(Product::class, 'sku')->whereNull('deleted_at'),
            ],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customerEmail' => 'pelanggan',
            'branch' => 'cabang',
            'fulfilment' => 'cara terima',
            'payment' => 'metode pembayaran',
            'status' => 'status',
            'shipping' => 'ongkos kirim',
            'note' => 'catatan',
            'items' => 'item pesanan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customerEmail.exists' => 'Pelanggan ini tidak terdaftar.',
            'branch.exists' => 'Cabang ini tidak terdaftar.',
            'items.required' => 'Pesanan harus memiliki minimal satu item.',
            'items.min' => 'Pesanan harus memiliki minimal satu item.',
            'items.*.productId.exists' => 'Salah satu produk pada pesanan tidak dikenali.',
            'items.*.qty.min' => 'Jumlah setiap item minimal 1.',
        ];
    }
}

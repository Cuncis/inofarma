<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use App\Support\CustomerStore;
use App\Support\ProductStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $emails = collect(app(CustomerStore::class)->all())->pluck('email')->all();
        $productIds = collect(app(ProductStore::class)->all())->pluck('id')->all();

        return [
            'customerEmail' => ['required', 'email', Rule::in($emails)],
            'payment' => ['required', Rule::in(Catalog::paymentMethods())],
            'status' => ['required', Rule::in(Catalog::orderStatuses())],
            'shipping' => ['required', 'integer', 'min:0', 'max:10000000'],
            'note' => ['nullable', 'string', 'max:500'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', Rule::in($productIds)],
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
            'customerEmail.in' => 'Pelanggan ini tidak terdaftar.',
            'items.required' => 'Pesanan harus memiliki minimal satu item.',
            'items.min' => 'Pesanan harus memiliki minimal satu item.',
            'items.*.productId.in' => 'Salah satu produk pada pesanan tidak dikenali.',
            'items.*.qty.min' => 'Jumlah setiap item minimal 1.',
        ];
    }
}

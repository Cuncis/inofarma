<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use App\Support\SellerStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = $this->route('seller');
        $others = collect(app(SellerStore::class)->all())
            ->reject(fn (array $seller) => $seller['id'] === $editing);

        return [
            'name' => ['required', 'string', 'max:80', Rule::notIn($others->pluck('name')->all())],
            'owner' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::notIn($others->pluck('email')->all())],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'license' => ['required', 'string', 'max:40', Rule::notIn($others->pluck('license')->all())],
            'city' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Catalog::sellerStatuses())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama toko',
            'owner' => 'nama pemilik',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'license' => 'nomor izin apotek',
            'city' => 'kota',
            'address' => 'alamat',
            'status' => 'status',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.not_in' => 'Nama toko ini sudah terdaftar.',
            'email.not_in' => 'Email ini sudah dipakai penjual lain.',
            'license.not_in' => 'Nomor izin apotek ini sudah terdaftar.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
        ];
    }
}

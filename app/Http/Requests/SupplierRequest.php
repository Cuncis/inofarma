<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = Supplier::where('code', $this->route('supplier'))->value('id');

        $unique = fn (string $column) => Rule::unique(Supplier::class, $column)
            ->ignore($editing)
            ->whereNull('deleted_at');

        return [
            'name' => ['required', 'string', 'max:80', $unique('name')],
            'owner' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', $unique('email')],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'license' => ['required', 'string', 'max:40', $unique('license_number')],
            'city' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::SUPPLIER_STATUSES))],
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
            'name.unique' => 'Nama toko ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah dipakai pemasok lain.',
            'license.unique' => 'Nomor izin apotek ini sudah terdaftar.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
        ];
    }
}

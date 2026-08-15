<?php

namespace App\Http\Requests;

use App\Support\Catalog;
use App\Support\CustomerStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = $this->route('customer');

        // Email identifies a customer's orders, so it has to stay unique —
        // except for the record currently being edited.
        $taken = collect(app(CustomerStore::class)->all())
            ->reject(fn (array $customer) => $customer['id'] === $editing)
            ->pluck('email')
            ->all();

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::notIn($taken)],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'city' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Catalog::customerStatuses())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama pelanggan',
            'email' => 'email',
            'phone' => 'nomor telepon',
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
            'email.not_in' => 'Email ini sudah dipakai pelanggan lain.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
        ];
    }
}

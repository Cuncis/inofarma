<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // Email is how a customer signs in, so it has to stay unique — except
        // for the record currently being edited.
        $editing = Customer::where('code', $this->route('customer'))->value('id');

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique(Customer::class, 'email')->ignore($editing)->whereNull('deleted_at'),
            ],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'city' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::CUSTOMER_STATUSES))],
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
            'email.unique' => 'Email ini sudah dipakai pelanggan lain.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
        ];
    }
}

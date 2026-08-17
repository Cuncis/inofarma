<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Coupon;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `branches` arrives as a list of branch codes and empty means "all branches"
 * — the form ships an empty array rather than omitting the key, so it stays
 * distinguishable from "field not sent".
 */
class CouponRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = Coupon::where('code', $this->route('coupon'))->value('id');

        return [
            'code' => [
                'required', 'string', 'max:40', 'regex:/^[A-Z0-9]+$/',
                Rule::unique(Coupon::class, 'code')->ignore($editing)->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(AdminOptions::labels(AdminOptions::COUPON_TYPES))],
            'value' => ['required_unless:type,Gratis Ongkir', 'nullable', 'integer', 'min:0', 'max:1000000000'],
            'minimumPurchase' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'startsAt' => ['nullable', 'date'],
            'expiresAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::COUPON_STATUSES))],
            'branches' => ['present', 'array'],
            'branches.*' => [Rule::exists(Branch::class, 'code')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode kupon',
            'type' => 'tipe',
            'value' => 'nilai',
            'minimumPurchase' => 'minimum belanja',
            'quota' => 'kuota',
            'startsAt' => 'tanggal mulai',
            'expiresAt' => 'berlaku sampai',
            'status' => 'status',
            'branches' => 'cabang berlaku',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode kupon ini sudah dipakai.',
            'code.regex' => 'Kode kupon hanya boleh berisi huruf kapital dan angka.',
            'value.required_unless' => 'Nilai kupon wajib diisi untuk tipe ini.',
            'expiresAt.after_or_equal' => 'Tanggal berlaku sampai tidak boleh sebelum tanggal mulai.',
        ];
    }
}

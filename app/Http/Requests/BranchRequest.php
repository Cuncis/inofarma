<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = Branch::where('code', $this->route('branch'))->value('id');

        return [
            'name' => ['required', 'string', 'max:120'],
            'addressLine' => ['required', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:80'],
            'kecamatan' => ['nullable', 'string', 'max:80'],
            'kota' => ['required', 'string', 'max:80'],
            'provinsi' => ['required', 'string', 'max:80'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'siaNumber' => ['nullable', 'string', 'max:60'],
            'apjName' => ['nullable', 'string', 'max:120'],
            'apjSipaNumber' => ['nullable', 'string', 'max:60'],
            'supportsDelivery' => ['required', 'boolean'],
            'supportsPickup' => ['required', 'boolean'],
            'deliveryRadiusKm' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in(AdminOptions::labels(AdminOptions::BRANCH_STATUSES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama cabang',
            'addressLine' => 'alamat',
            'kota' => 'kota',
            'provinsi' => 'provinsi',
            'latitude' => 'lintang',
            'longitude' => 'bujur',
            'deliveryRadiusKm' => 'radius pengantaran',
            'status' => 'status',
            'supportsDelivery' => 'dukungan antar',
            'supportsPickup' => 'dukungan ambil di tempat',
        ];
    }
}

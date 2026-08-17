<?php

namespace App\Http\Requests;

use App\Models\Attribute;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = Attribute::where('slug', $this->route('attribute'))->value('id');

        return [
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique(Attribute::class, 'name')->ignore($editing)->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(AdminOptions::labels(AdminOptions::ATTRIBUTE_TYPES))],
            'values' => ['required_if:type,Pilihan', 'nullable', 'array'],
            'values.*' => ['string', 'max:60', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama atribut',
            'type' => 'tipe',
            'values' => 'nilai',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Atribut dengan nama ini sudah ada.',
            'values.required_if' => 'Atribut bertipe pilihan wajib memiliki setidaknya satu nilai.',
        ];
    }
}

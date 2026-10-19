<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackChatOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email:rfc', 'max:190'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['order_number' => 'nomor pesanan', 'email' => 'alamat email'];
    }
}

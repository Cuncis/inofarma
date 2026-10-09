<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * `website` is a honeypot: the widget never fills it, a form-filling bot does.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'message' => ['required', 'string', 'max:1000'],
            'website' => ['nullable', 'string', 'max:0'],
            'subscribe' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nama', 'email' => 'email', 'message' => 'pesan'];
    }
}

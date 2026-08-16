<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique(User::class, 'email')->ignore($staff),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$staff ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'branchId' => ['nullable', Rule::exists(Branch::class, 'id')],
            'isActive' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => [Rule::exists(Role::class, 'name')],
        ];
    }
}

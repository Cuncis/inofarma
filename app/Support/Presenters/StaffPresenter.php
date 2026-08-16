<?php

namespace App\Support\Presenters;

use App\Models\User;

class StaffPresenter
{
    /**
     * @param  iterable<User>  $staff
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $staff): array
    {
        return collect($staff)->map(fn (User $user) => self::toArray($user))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'branchId' => $user->branch_id,
            'branchName' => $user->branch?->name ?? 'Pusat (semua cabang)',
            'isActive' => $user->is_active,
            'twoFactorEnabled' => $user->hasEnabledTwoFactor(),
            'roles' => $user->roles->pluck('name')->values()->all(),
            'lastLoginAt' => $user->last_login_at?->translatedFormat('d M Y, H:i'),
        ];
    }
}

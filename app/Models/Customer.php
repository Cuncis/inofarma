<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun pelanggan — sengaja terpisah dari `User`, yang menampung staf admin.
 *
 * Jumlah pesanan dan total belanja diturunkan dari relasi `orders`, tidak
 * disimpan sebagai kolom, supaya tidak pernah melenceng dari kenyataan.
 */
class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'email', 'phone', 'password', 'avatar_path',
        'preferred_branch_id', 'status', 'consent_at', 'consent_version',
        'allows_location',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'consent_at' => 'datetime',
            'allows_location' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress(): HasMany
    {
        return $this->addresses()->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function preferredBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'preferred_branch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Total belanja seumur hidup.
     *
     * Menghitung setiap pesanan yang masih berlaku, termasuk yang belum dibayar
     * atau masih diproses — angka ini dipakai untuk menilai nilai pelanggan,
     * jadi pesanan yang sedang berjalan tetap dihitung. Yang dibatalkan dan yang
     * kedaluwarsa tidak, karena uangnya memang tidak pernah berpindah.
     */
    public function getLifetimeSpendAttribute(): int
    {
        return (int) $this->orders()
            ->whereNotIn('status', ['dibatalkan', 'kedaluwarsa'])
            ->sum('grand_total');
    }
}

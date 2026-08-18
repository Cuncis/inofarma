<?php

namespace App\Models;

use App\Notifications\CustomerResetPassword;
use App\Notifications\CustomerVerifyEmail;
use App\Support\Auth\Scopes\CustomerBranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Akun pelanggan — sengaja terpisah dari `User`, yang menampung staf admin.
 *
 * Jumlah pesanan dan total belanja diturunkan dari relasi `orders`, tidak
 * disimpan sebagai kolom, supaya tidak pernah melenceng dari kenyataan.
 *
 * Query-scoped to a branch-confined staff member's own branch (Fase 3.2) via
 * `CustomerBranchScope`, based on where the customer has actually ordered —
 * not a `branch_id` on the row, since a customer isn't owned by one branch
 * the way stock is. A brand new customer with no orders yet is only visible
 * to central (unscoped) staff until their first order lands; that's a known,
 * accepted edge of the "pelanggan cabang" definition, not a bug.
 */
class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CustomerBranchScope);
    }

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
            'phone_otp_expires_at' => 'datetime',
            'consent_at' => 'datetime',
            'allows_location' => 'boolean',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomerVerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerResetPassword($token));
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * There is no SMS gateway wired into this project yet — see
     * `App\Http\Controllers\Shop\AuthController::sendPhoneOtp()` for where the
     * code actually goes in the meantime (the log, not a text message).
     */
    public function issuePhoneOtp(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'phone_otp_code' => hash('sha256', $code),
            'phone_otp_expires_at' => now()->addMinutes(10),
        ])->save();

        return $code;
    }

    public function verifyPhoneOtp(string $code): bool
    {
        if (! $this->phone_otp_code || ! $this->phone_otp_expires_at || $this->phone_otp_expires_at->isPast()) {
            return false;
        }

        if (! hash_equals($this->phone_otp_code, hash('sha256', $code))) {
            return false;
        }

        $this->forceFill([
            'phone_verified_at' => now(),
            'phone_otp_code' => null,
            'phone_otp_expires_at' => null,
        ])->save();

        return true;
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

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
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

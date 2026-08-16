<?php

namespace App\Models;

use App\Support\Auth\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pesanan dari satu cabang, diantar atau diambil sendiri.
 *
 * Angka uang di sini adalah snapshot saat pesanan dibuat, bukan hasil hitung
 * ulang dari data hidup. Pesanan lama harus tetap menunjukkan apa yang benar
 * benar dibayar walau harga produk sudah berubah.
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'number', 'branch_id', 'customer_id', 'fulfilment', 'status',
        'payment_method', 'payment_status', 'subtotal', 'discount_total',
        'shipping_total', 'tax_total', 'grand_total', 'recipient_name',
        'recipient_phone', 'shipping_address', 'shipping_latitude',
        'shipping_longitude', 'note', 'paid_at', 'ready_at', 'completed_at',
        'cancelled_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'shipping_total' => 'integer',
            'tax_total' => 'integer',
            'grand_total' => 'integer',
            'paid_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
            'shipping_latitude' => 'decimal:7',
            'shipping_longitude' => 'decimal:7',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeForBranch(Builder $query, Branch|int $branch): Builder
    {
        return $query->where('branch_id', $branch instanceof Branch ? $branch->id : $branch);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['selesai', 'dibatalkan', 'kedaluwarsa']);
    }

    public function getIsPickupAttribute(): bool
    {
        return $this->fulfilment === 'ambil';
    }

    /** Pesanan selesai adalah catatan keuangan — jangan dihapus, batalkan saja. */
    public function getIsDeletableAttribute(): bool
    {
        return $this->status !== 'selesai';
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}

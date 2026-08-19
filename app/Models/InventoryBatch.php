<?php

namespace App\Models;

use App\Support\Auth\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu batch obat di satu cabang, dengan tanggal kedaluwarsanya.
 */
class InventoryBatch extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'branch_id', 'product_id', 'batch_number', 'expires_at',
        'quantity', 'cost_price', 'received_at', 'expiry_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'received_at' => 'date',
            'quantity' => 'integer',
            'cost_price' => 'integer',
            'expiry_reminder_sent_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Urutan FEFO — yang paling cepat kedaluwarsa diambil lebih dulu.
     *
     * Ini bukan preferensi tapi keharusan: mengambil batch yang masih lama
     * kedaluwarsanya lebih dulu berarti batch lain akan kedaluwarsa di rak.
     */
    public function scopeFefo(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0)->orderBy('expires_at');
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->where('quantity', '>', 0)
            ->whereDate('expires_at', '<=', now()->addDays($days))
            ->whereDate('expires_at', '>=', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0)->whereDate('expires_at', '<', now());
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expires_at, false);
    }
}

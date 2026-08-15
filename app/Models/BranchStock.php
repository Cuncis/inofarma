<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stok satu produk di satu cabang.
 *
 * `available` adalah yang boleh dijual: jumlah fisik dikurangi yang sudah
 * dialokasikan ke pesanan yang belum diserahkan.
 */
class BranchStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'product_id', 'quantity', 'reserved_quantity',
        'reorder_point', 'price_override', 'is_listed',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'reorder_point' => 'integer',
            'price_override' => 'integer',
            'is_listed' => 'boolean',
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

    /** Jumlah yang benar-benar bisa dijual sekarang. */
    public function getAvailableAttribute(): int
    {
        return max($this->quantity - $this->reserved_quantity, 0);
    }

    /** Harga berlaku di cabang ini — penimpaan bila ada, kalau tidak harga nasional. */
    public function getEffectivePriceAttribute(): int
    {
        return $this->price_override ?? $this->product->price;
    }

    public function getIsLowAttribute(): bool
    {
        return $this->reorder_point > 0 && $this->available <= $this->reorder_point;
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '>', 'reserved_quantity');
    }
}

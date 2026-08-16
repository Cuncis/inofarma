<?php

namespace App\Models;

use App\Support\Auth\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Satu baris buku besar stok.
 *
 * `quantity` bertanda: positif menambah, negatif mengurangi. Tidak pernah
 * diubah setelah dibuat — kalau salah, catat koreksi baru, jangan sunting.
 */
class InventoryMovement extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'branch_id', 'product_id', 'inventory_batch_id', 'type', 'quantity',
        'balance_after', 'reference_type', 'reference_id', 'note', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance_after' => 'integer',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use App\Support\Auth\Scopes\TransferBranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu permintaan pindah stok dari satu cabang ke cabang lain.
 *
 * Barang dianggap "di jalan" antara `dikirim` dan `diterima`: sudah berkurang
 * dari cabang asal, belum bertambah di cabang tujuan. Lihat
 * `App\Support\Inventory\StockTransferManager` untuk transisi statusnya —
 * jangan ubah `status` langsung di sini, itu melewati pencatatan pergerakan.
 */
class StockTransfer extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TransferBranchScope);
    }

    protected $fillable = [
        'code', 'from_branch_id', 'to_branch_id', 'product_id', 'quantity',
        'status', 'requested_by', 'note', 'batches_shipped',
        'shipped_at', 'received_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'batches_shipped' => 'array',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['diminta', 'dikirim']);
    }

    public function getCanBeShippedAttribute(): bool
    {
        return $this->status === 'diminta';
    }

    public function getCanBeReceivedAttribute(): bool
    {
        return $this->status === 'dikirim';
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return $this->status === 'diminta';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Produk katalog nasional.
 *
 * Perhatikan tidak ada properti `stock`. Stok milik kombinasi produk × cabang
 * dan diakses lewat relasi `stocks()` atau `stockAt($branch)`. Kalau suatu saat
 * tergoda menambahkan kolom `stock` di sini, jangan — itu menghancurkan seluruh
 * model multi-cabang.
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'slug', 'category_id', 'supplier_id',
        'price', 'old_price', 'cost_price', 'unit', 'blurb', 'description',
        'drug_class', 'nie_bpom', 'composition', 'indication', 'dosage',
        'side_effects', 'warning', 'manufacturer', 'requires_prescription',
        'max_qty_per_order', 'storage', 'weight_grams', 'sold_count', 'rating', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'old_price' => 'integer',
            'cost_price' => 'integer',
            'weight_grams' => 'integer',
            'sold_count' => 'integer',
            'rating' => 'decimal:2',
            'requires_prescription' => 'boolean',
            'max_qty_per_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /** Baris stok produk ini di satu cabang, bila ada. */
    public function stockAt(Branch|int $branch): ?BranchStock
    {
        $id = $branch instanceof Branch ? $branch->id : $branch;

        return $this->stocks()->where('branch_id', $id)->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /** Produk yang benar-benar bisa dibeli di suatu cabang. */
    public function scopeAvailableAt(Builder $query, Branch|int $branch): Builder
    {
        $id = $branch instanceof Branch ? $branch->id : $branch;

        return $query->whereHas('stocks', fn (Builder $stock) => $stock
            ->where('branch_id', $id)
            ->where('is_listed', true)
            ->whereColumn('quantity', '>', 'reserved_quantity')
        );
    }

    /** Obat bebas terbatas wajib menampilkan peringatan P1–P6. */
    public function getNeedsWarningLabelAttribute(): bool
    {
        return $this->drug_class === 'bebas terbatas';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengaturan toko. `branch_id` null berarti berlaku nasional.
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'key', 'value', 'type'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Ambil nilai, dengan penimpaan per cabang bila ada. */
    public static function get(string $key, ?int $branchId = null, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)
            ->where(fn ($query) => $query->where('branch_id', $branchId)->orWhereNull('branch_id'))
            ->orderByRaw('branch_id IS NULL')
            ->first();

        return $setting?->value ?? $default;
    }
}

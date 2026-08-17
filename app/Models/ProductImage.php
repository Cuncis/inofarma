<?php

namespace App\Models;

use App\Support\ProductImageUploader;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'path', 'alt', 'position', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The 400×400 crop generated alongside `path` at upload time. Seeded/demo
     * images (static files under `public/media`) never had one made, so this
     * falls back to the full-size path rather than a broken link.
     */
    public function getThumbPathAttribute(): string
    {
        $thumb = ProductImageUploader::thumbPath($this->path);

        return str_starts_with($this->path, '/media/') ? $this->path : $thumb;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu apotek fisik dengan izin dan apoteker penanggung jawab sendiri.
 */
class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'slug', 'address_line', 'kelurahan', 'kecamatan',
        'kota', 'provinsi', 'postal_code', 'latitude', 'longitude', 'maps_url',
        'phone', 'whatsapp', 'sia_number', 'apj_name', 'apj_sipa_number',
        'operating_hours', 'supports_delivery', 'supports_pickup',
        'delivery_radius_km', 'status',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours' => 'array',
            'supports_delivery' => 'boolean',
            'supports_pickup' => 'boolean',
            'delivery_radius_km' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Hanya cabang yang sedang melayani. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Urutkan menurut jarak dari sebuah titik, dan sertakan jaraknya dalam km.
     *
     * Memakai rumus Haversine langsung di SQL supaya pengurutan dan pembatasan
     * terjadi di basis data, bukan setelah semua baris ditarik ke PHP. Dengan
     * sepuluh cabang ini berlebihan; dengan seribu ini yang membuatnya tetap cepat.
     */
    public function scopeNearest(Builder $query, float $latitude, float $longitude): Builder
    {
        return $query
            ->selectRaw('branches.*, (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance_km');
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line,
            $this->kelurahan,
            $this->kecamatan,
            $this->kota,
            $this->provinsi,
            $this->postal_code,
        ])->filter()->implode(', ');
    }
}

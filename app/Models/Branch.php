<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        'practice_schedule', 'store_schedule', 'photo_path', 'operating_hours', 'supports_delivery', 'supports_pickup',
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
            ->selectRaw('branches.*, '.self::distanceSql($query).' AS distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance_km');
    }

    /**
     * Adds `distance_km` like {@see scopeNearest()} but keeps every branch: one
     * whose coordinates are not filled in yet simply has a null distance
     * instead of dropping out of the list.
     */
    public function scopeWithDistanceFrom(Builder $query, float $latitude, float $longitude): Builder
    {
        return $query->selectRaw('branches.*, CASE WHEN latitude IS NULL OR longitude IS NULL THEN NULL ELSE '.self::distanceSql($query).' END AS distance_km', [$latitude, $longitude, $latitude]);
    }

    /**
     * Haversine distance in km from the bound point (lat, lng, lat) to the row.
     *
     * The cosine is clamped to [-1, 1] first: at the branch's own coordinates
     * rounding can give 1.0000000000000002, which MySQL and SQLite turn into a
     * null distance and PostgreSQL rejects with an error. SQLite has no
     * LEAST/GREATEST, but its multi-argument MIN/MAX do the same.
     */
    private static function distanceSql(Builder $query): string
    {
        [$least, $greatest] = $query->getConnection()->getDriverName() === 'sqlite' ? ['min', 'max'] : ['least', 'greatest'];

        return "(6371 * acos({$least}(1, {$greatest}(-1,
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            ))))";
    }

    /** Apotek yang tampil di toko dengan foto: kartu beranda dan halaman apoteknya. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->active()->whereNotNull('photo_path');
    }

    /**
     * Terdekat dulu dari sebuah titik bila titiknya diketahui; cabang tanpa
     * koordinat ikut di urutan terakhir. Tanpa titik, urut nama.
     */
    public function scopeClosestTo(Builder $query, ?float $latitude, ?float $longitude): Builder
    {
        if ($latitude === null || $longitude === null) {
            return $query->orderBy('name');
        }

        // Sorts on the coordinates rather than `distance_km IS NULL`: PostgreSQL
        // only accepts a select alias as a bare ORDER BY term, never inside an expression.
        return $query->withDistanceFrom($latitude, $longitude)
            ->orderByRaw('latitude IS NULL OR longitude IS NULL')
            ->orderBy('distance_km')
            ->orderBy('name');
    }

    /**
     * Slug untuk halaman apotek di toko: nama tanpa awalan "Apotek Inofarma",
     * jadi alamatnya `/apotek/jengki`, bukan `/apotek/apotek-inofarma-jengki`.
     */
    public static function slugSource(string $name): string
    {
        return Str::after($name, 'Apotek Inofarma ') ?: $name;
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

    /** Indonesian day key for each ISO weekday, matching `operating_hours`. */
    private const DAY_KEYS = [
        0 => 'minggu', 1 => 'senin', 2 => 'selasa', 3 => 'rabu',
        4 => 'kamis', 5 => 'jumat', 6 => 'sabtu',
    ];

    /**
     * Whether the branch's posted hours cover a given moment.
     *
     * Only the hours are checked here — `status` (tutup sementara/permanen)
     * is a separate fact, checked by {@see getIsOpenNowAttribute()}.
     */
    public function isOpenAt(CarbonInterface $at): bool
    {
        $hours = $this->operating_hours[self::DAY_KEYS[$at->dayOfWeek]] ?? null;

        if (! $hours) {
            return false;
        }

        $open = Carbon::parse($at->toDateString().' '.$hours['open'], $at->timezone);
        $close = Carbon::parse($at->toDateString().' '.$hours['close'], $at->timezone);

        return $at->between($open, $close);
    }

    public function getIsOpenNowAttribute(): bool
    {
        return $this->status === 'aktif' && $this->isOpenAt(now());
    }

    /** "08:00 - 21:00" for today, or null if the branch has no posted hours today — for "Halaman Cabang" (Fase 9.1). */
    public function getTodaysHoursAttribute(): ?string
    {
        $hours = $this->operating_hours[self::DAY_KEYS[now()->dayOfWeek]] ?? null;

        return $hours ? "{$hours['open']} - {$hours['close']}" : null;
    }
}

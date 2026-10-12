<?php

namespace App\Models;

use App\Support\Auth\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * The happy path of an order, in order. Delivery goes through a packing and
     * a shipping step; pickup goes straight from paid to ready for collection.
     * Either can instead end as `dibatalkan`, or `kedaluwarsa` when a payment or
     * pickup deadline ran out.
     */
    public const FLOW_DELIVERY = ['menunggu pembayaran', 'pembayaran diterima', 'siap dikirim', 'dikirim', 'selesai'];

    public const FLOW_PICKUP = ['menunggu pembayaran', 'pembayaran diterima', 'siap diambil', 'selesai'];

    /** Statuses that only exist once the customer has paid. */
    public const PAID_STATUSES = ['pembayaran diterima', 'siap dikirim', 'siap diambil', 'dikirim', 'selesai'];

    public const END_STATUSES = ['dibatalkan', 'kedaluwarsa'];

    /**
     * The statuses an order can be in, for one way of receiving it.
     *
     * @return list<string>
     */
    public static function statusesFor(string $fulfilment): array
    {
        return [...($fulfilment === 'ambil' ? self::FLOW_PICKUP : self::FLOW_DELIVERY), ...self::END_STATUSES];
    }

    /**
     * What `payment_status` an order in `$status` must have when staff set the
     * status by hand. Anything past "menunggu pembayaran" is paid; a refund is
     * never undone by an edit, and a cancelled or expired order keeps whatever
     * its payment was.
     */
    public static function paymentStatusFor(string $status, ?string $current = null): string
    {
        return match (true) {
            $current === 'refund' => 'refund',
            in_array($status, self::PAID_STATUSES, true) => 'lunas',
            in_array($status, self::END_STATUSES, true) => $current ?? 'belum bayar',
            default => 'belum bayar',
        };
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'number', 'branch_id', 'customer_id', 'coupon_id', 'fulfilment', 'status',
        'payment_method', 'payment_status', 'subtotal', 'discount_total',
        'shipping_total', 'tax_total', 'grand_total', 'recipient_name',
        'recipient_phone', 'shipping_address', 'shipping_latitude',
        'shipping_longitude', 'note', 'paid_at', 'ready_at', 'completed_at',
        'cancelled_at', 'expires_at', 'pickup_code', 'pickup_code_expires_at',
        'picked_up_at', 'handed_over_by', 'pickup_reminder_sent_at',
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
            'pickup_code_expires_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'pickup_reminder_sent_at' => 'datetime',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    /** The most recent payment attempt, if the order has any — for "lanjutkan pembayaran". */
    public function getLatestPaymentAttribute(): ?Payment
    {
        return $this->relationLoaded('payments')
            ? $this->payments->sortByDesc('id')->first()
            : $this->payments()->latest('id')->first();
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

    /**
     * Pelanggan hanya boleh membatalkan sendiri sebelum apotek mulai
     * memprosesnya — begitu pembayaran diterima barang sudah mulai disiapkan.
     */
    public function getIsCancellableByCustomerAttribute(): bool
    {
        return $this->status === 'menunggu pembayaran';
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getIsPickupCodeExpiredAttribute(): bool
    {
        return $this->pickup_code_expires_at !== null && $this->pickup_code_expires_at->isPast();
    }
}

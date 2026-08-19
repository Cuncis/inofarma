<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Biteship courier booking for one `antar` order. See the
 * `create_shipments_table` migration for the checkout-time-quote vs.
 * admin-time-booking split.
 */
class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'courier_company', 'courier_type', 'courier_name',
        'courier_service_name', 'price', 'biteship_order_id', 'tracking_id',
        'waybill_id', 'courier_link', 'status', 'history', 'raw_response',
        'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'history' => 'array',
            'raw_response' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** True once an admin has actually booked the pickup with Biteship, not merely quoted a price. */
    public function getIsBookedAttribute(): bool
    {
        return $this->biteship_order_id !== null;
    }
}

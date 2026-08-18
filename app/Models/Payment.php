<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One attempt at paying for an `Order` through a gateway. See the migration's
 * docblock for why this is its own table rather than columns on `orders`.
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'gateway', 'invoice_number', 'status', 'amount',
        'request_id', 'token_id', 'channel', 'checkout_url', 'expires_at',
        'paid_at', 'refunded_at', 'refund_note', 'raw_response', 'raw_notification',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'raw_response' => 'array',
            'raw_notification' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'pending' && $this->expires_at !== null && $this->expires_at->isPast();
    }
}

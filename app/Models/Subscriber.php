<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Someone who receives the newsletter. Opting out only flips the status, the
 * row stays, so the address can never be quietly re-subscribed by an import.
 */
class Subscriber extends Model
{
    use HasFactory;

    public const SUBSCRIBED = 'berlangganan';

    public const UNSUBSCRIBED = 'berhenti';

    protected $fillable = ['email', 'status', 'subscribed_at', 'unsubscribed_at', 'unsubscribe_token'];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            $subscriber->email = Str::lower(trim($subscriber->email));
            $subscriber->unsubscribe_token ??= Str::random(64);

            if ($subscriber->status === self::SUBSCRIBED || $subscriber->status === null) {
                $subscriber->subscribed_at ??= now();
            }
        });
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('status', self::SUBSCRIBED);
    }

    public function isSubscribed(): bool
    {
        return $this->status === self::SUBSCRIBED;
    }

    public function unsubscribe(): void
    {
        if (! $this->isSubscribed()) {
            return;
        }

        $this->update(['status' => self::UNSUBSCRIBED, 'unsubscribed_at' => now()]);
    }
}

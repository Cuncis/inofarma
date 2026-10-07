<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Newsletter extends Model
{
    use HasFactory;

    public const DRAFT = 'draf';

    public const SENDING = 'mengirim';

    public const SENT = 'terkirim';

    public const FAILED = 'gagal';

    protected $fillable = [
        'name', 'subject', 'preview_text', 'content', 'custom_html', 'status', 'sent_at',
        'recipient_count', 'success_count', 'failed_count',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }
}

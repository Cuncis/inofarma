<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Satu percakapan antara seorang pengunjung dan admin. Pengunjung tidak punya
 * akun: nama dan email dari formulir chat, serta `token` yang disimpan di
 * browsernya, itulah yang menghubungkannya kembali ke percakapan ini.
 */
class ChatConversation extends Model
{
    use HasFactory;

    public const OPEN = 'terbuka';

    public const CLOSED = 'ditutup';

    protected $fillable = ['token', 'name', 'email', 'status', 'admin_unread_count', 'last_message_at'];

    protected function casts(): array
    {
        return [
            'admin_unread_count' => 'integer',
            'last_message_at' => 'datetime',
        ];
    }

    public static function newToken(): string
    {
        return Str::random(40);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    /** Percakapan dengan pesan pengunjung yang belum dibaca admin. */
    public function scopeNeedsReply(Builder $query): Builder
    {
        return $query->where('admin_unread_count', '>', 0);
    }
}

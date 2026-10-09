<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu pesan dalam percakapan chat, dari pengunjung atau dari admin.
 */
class ChatMessage extends Model
{
    use HasFactory;

    public const FROM_VISITOR = 'pengunjung';

    public const FROM_ADMIN = 'admin';

    protected $fillable = ['chat_conversation_id', 'sender', 'user_id', 'body', 'visitor_seen_at', 'emailed_at'];

    protected function casts(): array
    {
        return [
            'visitor_seen_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromAdmin(): bool
    {
        return $this->sender === self::FROM_ADMIN;
    }
}

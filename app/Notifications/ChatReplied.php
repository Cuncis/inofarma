<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Email to a chat visitor who has not seen the admin's reply in the chat
 * window. Sent on demand to the address from the chat form, and only after a
 * delay (`config('chat.email_delay_minutes')`): by then a visitor with the
 * window open has long since received the reply, and nothing is sent.
 *
 * Fired by `App\Observers\ChatMessageObserver`.
 */
class ChatReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ChatMessage $message) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @return array<string, Carbon> */
    public function withDelay(object $notifiable): array
    {
        return ['mail' => now()->addMinutes(config('chat.email_delay_minutes'))];
    }

    /**
     * Skips a reply the visitor already saw in the chat, or one that went out
     * in an earlier email together with its neighbours.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        $message = $this->message->fresh();

        return $message !== null && $message->visitor_seen_at === null && $message->emailed_at === null;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;

        $pending = $conversation->messages()
            ->where('sender', ChatMessage::FROM_ADMIN)
            ->whereNull('visitor_seen_at')
            ->whereNull('emailed_at')
            ->get();

        $conversation->messages()->whereIn('id', $pending->modelKeys())->update(['emailed_at' => now()]);

        $mail = (new MailMessage)
            ->subject('Balasan dari Apotek Inofarma')
            ->greeting("Halo {$conversation->name},")
            ->line('Admin Apotek Inofarma membalas pesan Anda:');

        foreach ($pending as $reply) {
            $mail->line('> '.str_replace("\n", "\n> ", $reply->body));
        }

        return $mail
            ->action('Lanjutkan chat', url('/').'?chat='.$conversation->token)
            ->line('Anda bisa membalas langsung dari tautan di atas. Tautan ini khusus untuk Anda, jangan dibagikan.');
    }
}

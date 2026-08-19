<?php

namespace App\Notifications\Channels;

use App\Support\Notifications\WhatsAppClient;
use Illuminate\Notifications\Notification;

/**
 * A custom Notification channel — referenced by its class name directly
 * from `via()` (e.g. `WhatsAppChannel::class`), no alias registration
 * needed. Resolves the recipient's phone through the standard
 * `routeNotificationForWhatsapp()` convention, same as Laravel's own `mail`
 * channel resolving `routeNotificationForMail()`.
 */
class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        // `Str::studly('whatsapp')` is `Whatsapp` — the recipient must define
        // `routeNotificationForWhatsapp()`, lowercase p, to be resolved here.
        $phone = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (! $phone) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        WhatsAppClient::make()->sendTemplate($phone, $message->template, $message->parameters, $message->logFallback);
    }
}

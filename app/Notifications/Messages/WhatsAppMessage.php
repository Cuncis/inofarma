<?php

namespace App\Notifications\Messages;

/**
 * What a `toWhatsApp()` method on a notification returns — a template name
 * plus its positional body parameters (Meta requires an approved template
 * for any business-initiated message), and a plain-text fallback for the
 * local-dev log line when no WhatsApp credentials are configured.
 */
final class WhatsAppMessage
{
    /**
     * @param  list<string>  $parameters
     */
    public function __construct(
        public readonly string $template,
        public readonly array $parameters,
        public readonly string $logFallback,
    ) {}
}

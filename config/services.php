<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | DOKU Checkout (Fase 6). `notification_path` must match, byte for byte,
    | the path portion of whatever Notification URL is configured in the DOKU
    | Back Office — it is signed as part of the webhook signature, so a
    | mismatch here fails every notification's signature check.
    */
    'doku' => [
        'client_id' => env('DOKU_CLIENT_ID'),
        'secret_key' => env('DOKU_SECRET_KEY'),
        'production' => env('DOKU_PRODUCTION', false),
        'base_url' => env('DOKU_PRODUCTION', false)
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com',
        'notification_path' => '/doku/notifikasi',
    ],

    /*
    | Biteship (Fase 7). Unlike DOKU, Biteship's webhook carries no signature
    | at all (confirmed against their own docs) — `webhook_token` is a secret
    | *this app* generates and appends to the Notification URL entered in the
    | Biteship dashboard (`?token=...`), which is the authentication mechanism
    | their own docs point integrators toward providing themselves. See
    | `Webhooks\BiteshipWebhookController`.
    */
    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => 'https://api.biteship.com/v1',
        'webhook_token' => env('BITESHIP_WEBHOOK_TOKEN'),
    ],

    /*
    | WhatsApp Cloud API (Fase 8) — Meta's own official Business API, not a
    | reseller, so this satisfies ROADMAP.md 8's "pakai penyedia resmi
    | WhatsApp Business API" directly. Sending a business-initiated message
    | (as opposed to replying inside a customer-opened 24-hour window)
    | requires a pre-approved message *template* per Meta's own rules —
    | `template` below names the ones this app expects to exist in the
    | connected WhatsApp Business Account. See `App\Support\Notifications\WhatsAppClient`.
    */
    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'base_url' => 'https://graph.facebook.com/v21.0',
        'templates' => [
            'order_shipped' => env('WHATSAPP_TEMPLATE_ORDER_SHIPPED', 'pesanan_dikirim'),
            'order_ready_for_pickup' => env('WHATSAPP_TEMPLATE_ORDER_READY', 'pesanan_siap_diambil'),
        ],
    ],

];

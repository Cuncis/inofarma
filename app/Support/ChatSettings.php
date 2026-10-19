<?php

namespace App\Support;

use App\Models\Setting;
use App\Support\Notifications\WhatsAppClient;

/**
 * What the storefront chat window says before a visitor writes anything: a
 * welcome message and a short list of ready-made questions shown above the
 * text field. Edited by staff from the Inbox; stored as national `Setting` rows.
 */
class ChatSettings
{
    public const WELCOME_KEY = 'chat.welcome_message';

    public const QUESTIONS_KEY = 'chat.quick_questions';

    public const MAX_QUESTIONS = 6;

    public const TYPE_MESSAGE = 'message';

    /** A shortcut that opens the in-chat "Lacak pesanan saya" form. */
    public const TYPE_TRACK_ORDER = 'track_order';

    /** Shows a fixed answer in the chat straight away, no admin needed (opening hours, delivery, and so on). */
    public const TYPE_AUTO_REPLY = 'auto_reply';

    /** Opens WhatsApp to the given number with the question as the first message. */
    public const TYPE_WHATSAPP = 'whatsapp';

    /** Dials the given number. */
    public const TYPE_PHONE = 'phone';

    /** Asks for a medicine name and sends "is it available?" as a chat message. */
    public const TYPE_ASK_PRODUCT = 'ask_product';

    public const TYPES = [
        self::TYPE_MESSAGE,
        self::TYPE_TRACK_ORDER,
        self::TYPE_AUTO_REPLY,
        self::TYPE_WHATSAPP,
        self::TYPE_PHONE,
        self::TYPE_ASK_PRODUCT,
    ];

    public const DEFAULT_WELCOME = 'Halo! Ada yang bisa kami bantu? Tulis pertanyaan Anda di bawah, admin kami siap membantu.';

    public static function welcomeMessage(): string
    {
        $message = trim((string) Setting::get(self::WELCOME_KEY, null, ''));

        return $message !== '' ? $message : self::DEFAULT_WELCOME;
    }

    /**
     * Each question may carry a link: tapping it opens the page instead of
     * sending the question as a message. Older rows stored plain strings.
     *
     * @return list<array{question: string, url: ?string, type: string, answer: ?string, phone: ?string}>
     */
    public static function quickQuestions(): array
    {
        $decoded = json_decode((string) Setting::get(self::QUESTIONS_KEY, null, '[]'), true);

        return collect(is_array($decoded) ? $decoded : [])
            ->map(fn ($item) => self::normalize($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<string|array{question?: mixed, url?: mixed, type?: mixed, answer?: mixed, phone?: mixed}>  $questions
     */
    public static function save(?string $welcome, array $questions): void
    {
        $questions = collect($questions)
            ->map(fn ($item) => self::normalize($item))
            ->filter()
            ->take(self::MAX_QUESTIONS)
            ->values()
            ->all();

        Setting::updateOrCreate(
            ['branch_id' => null, 'key' => self::WELCOME_KEY],
            ['value' => trim((string) $welcome), 'type' => 'string'],
        );

        Setting::updateOrCreate(
            ['branch_id' => null, 'key' => self::QUESTIONS_KEY],
            ['value' => json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'type' => 'json'],
        );
    }

    /**
     * @return array{question: string, url: ?string, type: string, answer: ?string, phone: ?string}|null
     */
    private static function normalize(mixed $item): ?array
    {
        $question = trim((string) (is_array($item) ? ($item['question'] ?? '') : $item));
        $url = is_array($item) ? trim((string) ($item['url'] ?? '')) : '';

        $type = is_array($item) && in_array($item['type'] ?? null, self::TYPES, true) ? $item['type'] : self::TYPE_MESSAGE;
        $answer = is_array($item) ? trim((string) ($item['answer'] ?? '')) : '';
        $phone = is_array($item) ? WhatsAppClient::normalizePhone((string) ($item['phone'] ?? '')) : '';

        // A shortcut that needs a detail it has not got is dropped rather than shown broken.
        if ($question === '' || ($type === self::TYPE_AUTO_REPLY && $answer === '') || (in_array($type, [self::TYPE_WHATSAPP, self::TYPE_PHONE], true) && $phone === '')) {
            return null;
        }

        return [
            'question' => $question,
            'url' => $type === self::TYPE_MESSAGE && $url !== '' ? $url : null,
            'type' => $type,
            'answer' => $type === self::TYPE_AUTO_REPLY ? $answer : null,
            'phone' => in_array($type, [self::TYPE_WHATSAPP, self::TYPE_PHONE], true) ? $phone : null,
        ];
    }

    /**
     * @return array{welcome: string, quickQuestions: list<array{question: string, url: ?string, type: string, answer: ?string, phone: ?string}>}
     */
    public static function forStorefront(): array
    {
        return ['welcome' => self::welcomeMessage(), 'quickQuestions' => self::quickQuestions()];
    }
}

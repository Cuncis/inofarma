<?php

namespace App\Support;

/**
 * The storefront FAQ: questions and answers edited under Konten Website > FAQ.
 * Until someone saves, the page shows {@see DEFAULTS}.
 */
class FaqContent
{
    public const KEY = 'content.faq';

    public const MAX_QUESTIONS = 40;

    /** The answer the FAQ shipped with for questions that had none of their own. */
    private const GENERIC_ANSWER = 'Tim kami siap membantu, hubungi kami lewat aplikasi dan kami akan membalas dalam satu hari kerja.';

    /** @var list<array{question: string, answer: string}> */
    public const DEFAULTS = [
        ['question' => 'Apakah saya bisa memesan secara online?', 'answer' => 'Tentu, Anda bisa memesan langsung lewat situs web atau aplikasi kami kapan saja.'],
        ['question' => 'Apakah tersedia produk perawatan kulit?', 'answer' => self::GENERIC_ANSWER],
        ['question' => 'Apakah ada layanan bungkus kado?', 'answer' => self::GENERIC_ANSWER],
        ['question' => 'Apakah produk Anda bebas uji coba hewan?', 'answer' => self::GENERIC_ANSWER],
        ['question' => 'Bagaimana cara melacak pesanan saya?', 'answer' => self::GENERIC_ANSWER],
    ];

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function all(): array
    {
        $saved = ContentStore::read(self::KEY);

        return array_key_exists('items', $saved) && is_array($saved['items']) ? self::clean($saved['items']) : self::DEFAULTS;
    }

    /**
     * @param  array<int|string, mixed>  $items  the admin form's repeater state
     */
    public static function save(array $items): void
    {
        ContentStore::write(self::KEY, ['items' => array_slice(self::clean($items), 0, self::MAX_QUESTIONS)]);
    }

    /**
     * Answers are split into paragraphs here, so the page only has to loop.
     *
     * @return list<array{question: string, paragraphs: list<string>}>
     */
    public static function forStorefront(): array
    {
        return array_map(fn (array $item) => [
            'question' => $item['question'],
            'paragraphs' => ContentStore::paragraphs($item['answer']),
        ], self::all());
    }

    /**
     * @param  array<int|string, mixed>  $items
     * @return list<array{question: string, answer: string}>
     */
    private static function clean(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => [
                'question' => trim((string) (is_array($item) ? ($item['question'] ?? '') : '')),
                'answer' => trim((string) (is_array($item) ? ($item['answer'] ?? '') : '')),
            ])
            ->filter(fn (array $item) => $item['question'] !== '' && $item['answer'] !== '')
            ->values()
            ->all();
    }
}

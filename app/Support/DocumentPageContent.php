<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * The storefront's document pages, edited under Konten Website: Info
 * Pengiriman and Kebijakan Pengembalian Dana (pages of titled sections), and
 * Syarat & Ketentuan and Kebijakan Privasi (popups of numbered sections and
 * clauses). Each is stored through {@see ContentStore}; until it is saved the
 * page shows its {@see DocumentPageDefaults}.
 *
 * These are compliance texts: when how the shop works changes (payment
 * provider, courier, time limits, refunds), the matching section should be
 * updated here.
 */
class DocumentPageContent
{
    public const SHIPPING = 'shipping';

    public const REFUND = 'refund';

    public const TERMS = 'terms';

    public const PRIVACY = 'privacy';

    /** Documents made of numbered sections and clauses, shown as a popup. */
    public const LEGAL = [self::TERMS, self::PRIVACY];

    public const DOCUMENTS = [self::SHIPPING, self::REFUND, self::TERMS, self::PRIVACY];

    public static function isLegal(string $document): bool
    {
        return in_array($document, self::LEGAL, true);
    }

    /**
     * Saved content over the defaults, in the shape the admin form edits.
     *
     * @return array<string, mixed>
     */
    public static function all(string $document): array
    {
        $defaults = self::defaults($document);
        $saved = array_intersect_key(ContentStore::read(self::key($document)), $defaults);

        return array_replace($defaults, $saved);
    }

    /**
     * Saves the admin form's state. A legal document whose text changed gets
     * this month as its "Terakhir diperbarui" date.
     *
     * @param  array<string, mixed>  $content
     */
    public static function save(string $document, array $content): void
    {
        $legal = self::isLegal($document);
        $current = self::all($document);

        $cleaned = [
            'title' => trim((string) ($content['title'] ?? '')) ?: $current['title'],
            'sections' => $legal
                ? self::cleanLegalSections((array) ($content['sections'] ?? []))
                : self::cleanSections((array) ($content['sections'] ?? [])),
        ];

        if ($legal) {
            $cleaned['contactHeading'] = trim((string) ($content['contactHeading'] ?? ''));
            $changed = $cleaned['sections'] !== $current['sections'] || $cleaned['contactHeading'] !== $current['contactHeading'];
            $cleaned['updatedAt'] = $changed ? now()->locale('id')->translatedFormat('F Y') : $current['updatedAt'];
        }

        ContentStore::write(self::key($document), $cleaned);
    }

    /**
     * What the storefront renders: paragraphs for a page, numbered sections,
     * clauses and bullet items plus the company block for a legal document.
     *
     * @return array<string, mixed>
     */
    public static function forStorefront(string $document): array
    {
        $content = self::all($document);

        if (! self::isLegal($document)) {
            return [
                'title' => $content['title'],
                'sections' => array_map(fn (array $section) => [
                    'title' => $section['title'],
                    'paragraphs' => ContentStore::paragraphs($section['body']),
                ], $content['sections']),
            ];
        }

        return [
            'title' => $content['title'],
            'sections' => array_map(fn (array $section, int $index) => [
                'title' => ($index + 1).'. '.$section['title'],
                'clauses' => array_map(fn (array $clause, int $clauseIndex) => [
                    'number' => ($index + 1).'.'.($clauseIndex + 1),
                    'text' => $clause['text'],
                    'items' => ContentStore::lines($clause['items']),
                ], $section['clauses'], array_keys($section['clauses'])),
            ], $content['sections'], array_keys($content['sections'])),
            'contactHeading' => $content['contactHeading'],
            'updatedAt' => $content['updatedAt'],
            'company' => SiteFooterContent::company(),
        ];
    }

    private static function key(string $document): string
    {
        return "content.document.{$document}";
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaults(string $document): array
    {
        return match ($document) {
            self::SHIPPING => DocumentPageDefaults::SHIPPING,
            self::REFUND => DocumentPageDefaults::REFUND,
            self::TERMS => DocumentPageDefaults::TERMS,
            self::PRIVACY => DocumentPageDefaults::PRIVACY,
            default => throw new InvalidArgumentException("Unknown document [{$document}]."),
        };
    }

    /**
     * @param  array<int|string, mixed>  $sections
     * @return list<array{title: string, body: string}>
     */
    private static function cleanSections(array $sections): array
    {
        return collect($sections)
            ->map(fn ($section) => [
                'title' => trim((string) ($section['title'] ?? '')),
                'body' => trim((string) ($section['body'] ?? '')),
            ])
            ->filter(fn (array $section) => $section['title'] !== '' && $section['body'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int|string, mixed>  $sections
     * @return list<array{title: string, clauses: list<array{text: string, items: string}>}>
     */
    private static function cleanLegalSections(array $sections): array
    {
        return collect($sections)
            ->map(fn ($section) => [
                'title' => trim((string) ($section['title'] ?? '')),
                'clauses' => collect((array) ($section['clauses'] ?? []))
                    ->map(fn ($clause) => [
                        'text' => trim((string) ($clause['text'] ?? '')),
                        'items' => implode("\n", ContentStore::lines((string) ($clause['items'] ?? ''))),
                    ])
                    ->filter(fn (array $clause) => $clause['text'] !== '')
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $section) => $section['title'] !== '' && $section['clauses'] !== [])
            ->values()
            ->all();
    }
}

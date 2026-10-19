<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Storage for the storefront content edited under Konten Website: each page's
 * content is one national `Setting` row holding JSON. The page classes
 * ({@see HomePageContent}, {@see FaqContent}, {@see DocumentPageContent},
 * {@see SiteFooterContent}) own their defaults and shape; this only reads,
 * writes and splits text.
 */
class ContentStore
{
    /**
     * @return array<string, mixed> the saved content, or an empty array when nothing (valid) is saved
     */
    public static function read(string $key): array
    {
        $saved = json_decode((string) Setting::get($key, null, '{}'), true);

        return is_array($saved) ? $saved : [];
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public static function write(string $key, array $content): void
    {
        Setting::updateOrCreate(
            ['branch_id' => null, 'key' => $key],
            ['value' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'type' => 'json'],
        );
    }

    /**
     * Text split on blank lines, as the editors ask staff to write paragraphs.
     *
     * @return list<string>
     */
    public static function paragraphs(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', trim($text)) ?: [])));
    }

    /**
     * Text split into its non-empty lines, for lists written one item per line.
     *
     * @return list<string>
     */
    public static function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', trim($text)) ?: [])));
    }
}

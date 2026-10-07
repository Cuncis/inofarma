<?php

namespace App\Support\Newsletters;

use App\Filament\RichContent\ButtonBlock;
use App\Models\Newsletter;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Turns a newsletter into the pieces of the email: its body HTML and any CSS
 * that has to live in the email's <head>. The one place that knows how the
 * editor is configured, so the editor, the preview and the sent email can
 * never disagree.
 */
class NewsletterRenderer
{
    public const ATTACHMENT_DISK = 'public';

    public const ATTACHMENT_DIRECTORY = 'newsletters';

    /**
     * @return array{body: string, styles: string, isCustom: bool}
     */
    public static function render(Newsletter $newsletter): array
    {
        if (filled(trim((string) $newsletter->custom_html))) {
            return [...self::custom((string) $newsletter->custom_html), 'isCustom' => true];
        }

        return ['body' => self::body($newsletter), 'styles' => '', 'isCustom' => false];
    }

    /**
     * Editor content rendered to email-safe HTML: images and links carry full
     * URLs, because an email has no "current site" to resolve /storage/... on.
     */
    public static function body(Newsletter $newsletter): string
    {
        $html = RichContentRenderer::make($newsletter->content ?? '')
            ->fileAttachmentsDisk(self::ATTACHMENT_DISK)
            ->fileAttachmentsVisibility('public')
            ->customBlocks([ButtonBlock::class])
            ->toHtml();

        return self::absoluteUrls($html);
    }

    /**
     * Pasted HTML is trusted as written (only an administrator can paste it),
     * with two exceptions: scripts never belong in an email, and a pasted
     * full document is split so its <style> blocks can go into our <head>
     * (mail apps drop <style> that sits in the middle of a body).
     *
     * @return array{body: string, styles: string}
     */
    public static function custom(string $html): array
    {
        $html = (string) preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
        $html = (string) preg_replace('#\s(on\w+)\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $html);
        $html = (string) preg_replace('#href\s*=\s*(["\'])\s*javascript:[^"\']*\1#i', 'href="#"', $html);

        $styles = '';

        $html = (string) preg_replace_callback('#<style\b[^>]*>.*?</style>#is', function (array $match) use (&$styles) {
            $styles .= $match[0]."\n";

            return '';
        }, $html);

        if (preg_match('#<body\b[^>]*>(.*)</body>#is', $html, $match)) {
            $html = $match[1];
        } else {
            $html = (string) preg_replace('#</?(?:!doctype|html|head|body|meta|title)\b[^>]*>#i', '', $html);
        }

        return ['body' => self::absoluteUrls(trim($html)), 'styles' => $styles];
    }

    public static function absoluteUrls(string $html): string
    {
        return (string) preg_replace_callback(
            '/\b(src|href)="(\/[^"\/][^"]*)"/i',
            fn (array $match) => $match[1].'="'.url($match[2]).'"',
            $html,
        );
    }
}

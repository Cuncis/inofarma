<?php

namespace App\Filament\RichContent;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\TextInput;

/**
 * The newsletter's call-to-action button. Email clients ignore most CSS, so it
 * is built from a table with inline styles, the one pattern they all render.
 *
 * The green is set in `style="background-color"`, not the `bgcolor` attribute:
 * the editor's HTML cleaner strips `bgcolor`, which left white text on white.
 */
class ButtonBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return 'button';
    }

    public static function getLabel(): string
    {
        return __('Tombol');
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalHeading(__('Tombol ajakan'))
            ->modalDescription(__('Tombol yang membawa pembaca ke sebuah halaman.'))
            ->schema([
                TextInput::make('label')
                    ->label(__('Teks Tombol'))
                    ->required()
                    ->maxLength(60),
                TextInput::make('url')
                    ->label(__('Link'))
                    ->url()
                    ->required()
                    ->placeholder('https://'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function getPreviewLabel(array $config): string
    {
        return __('Tombol').': '.($config['label'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function toPreviewHtml(array $config): ?string
    {
        return static::button($config);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    public static function toHtml(array $config, array $data): ?string
    {
        return static::button($config);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private static function button(array $config): string
    {
        $label = e($config['label'] ?? '');
        $url = e($config['url'] ?? '#');

        return <<<HTML
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px auto;">
            <tr>
                <td align="center" style="background-color:#24CE30;border-radius:2px;">
                    <a href="{$url}" target="_blank" style="display:inline-block;padding:14px 32px;background-color:#24CE30;border-radius:2px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;">{$label}</a>
                </td>
            </tr>
        </table>
        HTML;
    }
}

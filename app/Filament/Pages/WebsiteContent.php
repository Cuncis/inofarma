<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * Konten Website: the storefront pages whose content staff can change without
 * touching code. Each page has its own editor under this menu
 * ({@see ContentEditorPage}), listed in {@see EDITORS}.
 */
class WebsiteContent extends Page
{
    protected string $view = 'filament.pages.website-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    /** Full width, so the image grids and repeaters have room. */
    protected Width|string|null $maxContentWidth = Width::Full;

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'konten-website';

    public static function getNavigationLabel(): string
    {
        return __('Konten Website');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Konten Website');
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Konten:Lihat');
    }

    /**
     * The editors listed here, in this order.
     *
     * @var list<class-string<ContentEditorPage>>
     */
    public const EDITORS = [
        EditHomePage::class,
        EditFaqPage::class,
        EditShippingInfoPage::class,
        EditRefundPolicyPage::class,
        EditTermsPage::class,
        EditPrivacyPolicyPage::class,
        EditSiteFooterPage::class,
    ];

    /**
     * @return list<array{title: string, description: string, icon: string|BackedEnum|null, url: string, storefrontUrl: ?string}>
     */
    public function editablePages(): array
    {
        return array_map(fn (string $editor) => [
            'title' => $editor::getNavigationLabel(),
            'description' => $editor::description(),
            'icon' => $editor::getNavigationIcon(),
            'url' => $editor::getUrl(),
            'storefrontUrl' => $editor::storefrontUrl(),
        ], self::EDITORS);
    }
}

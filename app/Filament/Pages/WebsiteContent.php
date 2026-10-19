<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * Konten Website: the storefront pages whose content staff can change without
 * touching code. Each page has its own editor under this menu; for now that
 * is only Beranda ({@see EditHomePage}).
 */
class WebsiteContent extends Page
{
    protected string $view = 'filament.pages.website-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

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
     * @return list<array{title: string, description: string, url: string, storefrontUrl: string}>
     */
    public function editablePages(): array
    {
        return [
            [
                'title' => __('Beranda'),
                'description' => __('Hero carousel, ikon kategori, banner promo, brand, keuntungan, testimoni, dan teks penutup.'),
                'url' => EditHomePage::getUrl(),
                'storefrontUrl' => route('home'),
            ],
        ];
    }
}

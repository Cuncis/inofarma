<?php

namespace App\Filament\Pages;

use App\Support\DocumentPageContent;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

/**
 * Konten Website > Syarat & Ketentuan.
 */
class EditTermsPage extends EditDocumentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'konten-website/syarat-ketentuan';

    protected static function document(): string
    {
        return DocumentPageContent::TERMS;
    }

    public static function getNavigationLabel(): string
    {
        return __('Syarat & Ketentuan');
    }

    public static function description(): string
    {
        return __('Popup di halaman Daftar, Checkout, dan Profil. Nomor pasal dibuat otomatis.');
    }

    public static function storefrontUrl(): ?string
    {
        return null;
    }
}

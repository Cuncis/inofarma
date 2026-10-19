<?php

namespace App\Filament\Pages;

use App\Support\DocumentPageContent;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

/**
 * Konten Website > Kebijakan Privasi.
 */
class EditPrivacyPolicyPage extends EditDocumentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'konten-website/kebijakan-privasi';

    protected static function document(): string
    {
        return DocumentPageContent::PRIVACY;
    }

    public static function getNavigationLabel(): string
    {
        return __('Kebijakan Privasi');
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

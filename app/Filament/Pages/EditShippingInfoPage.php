<?php

namespace App\Filament\Pages;

use App\Support\DocumentPageContent;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

/**
 * Konten Website > Info Pengiriman.
 */
class EditShippingInfoPage extends EditDocumentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'konten-website/info-pengiriman';

    protected static function document(): string
    {
        return DocumentPageContent::SHIPPING;
    }

    public static function getNavigationLabel(): string
    {
        return __('Info Pengiriman');
    }

    public static function description(): string
    {
        return __('Ongkos kirim, area, estimasi waktu, ambil di toko, dan metode pembayaran.');
    }

    public static function storefrontUrl(): ?string
    {
        return route('ui.shipping-info');
    }
}

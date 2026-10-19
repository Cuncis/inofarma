<?php

namespace App\Filament\Pages;

use App\Support\DocumentPageContent;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

/**
 * Konten Website > Kebijakan Pengembalian Dana.
 */
class EditRefundPolicyPage extends EditDocumentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'konten-website/pengembalian-dana';

    protected static function document(): string
    {
        return DocumentPageContent::REFUND;
    }

    public static function getNavigationLabel(): string
    {
        return __('Kebijakan Pengembalian Dana');
    }

    public static function description(): string
    {
        return __('Kapan, bagaimana, dan berapa lama dana dikembalikan.');
    }

    public static function storefrontUrl(): ?string
    {
        return route('ui.kebijakan-pengembalian-dana');
    }
}

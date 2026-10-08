<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\StockMatrixWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * One product × every branch, at a glance — read-only, see
 * StockMatrixController's docblock. All the actual work happens in
 * StockMatrixWidget; this page just hosts it.
 */
class StockMatrix extends Page
{
    protected string $view = 'filament.pages.stock-matrix';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?int $navigationSort = 5;

    public static function getNavigationParentItem(): ?string
    {
        return ProductResource::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Matriks Stok');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Matriks Stok');
    }

    protected static ?string $slug = 'inventaris/matriks';

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Inventaris:Lihat');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockMatrixWidget::class,
        ];
    }
}

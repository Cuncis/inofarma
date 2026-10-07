<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StockMatrixWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * One product × every branch, at a glance — read-only, see
 * StockMatrixController's docblock. All the actual work happens in
 * StockMatrixWidget; this page just hosts it.
 */
class StockMatrix extends Page
{
    protected string $view = 'filament.pages.stock-matrix';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Inventaris');
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

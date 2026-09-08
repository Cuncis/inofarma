<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StockMatrixWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $navigationLabel = 'Matriks Stok';

    protected static ?string $title = 'Matriks Stok';

    protected static ?string $slug = 'inventaris/matriks';

    protected function getHeaderWidgets(): array
    {
        return [
            StockMatrixWidget::class,
        ];
    }
}

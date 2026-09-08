<?php

namespace App\Filament\Resources\BranchStocks;

use App\Filament\Resources\BranchStocks\Pages\ListBranchStocks;
use App\Filament\Resources\BranchStocks\Tables\BranchStocksTable;
use App\Models\BranchStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Per-branch stock: view what one branch is holding, correct it, or receive
 * new stock into it. See BranchStockController's docblock for why the
 * cross-branch matrix (StockMatrix) is a separate screen.
 *
 * `BranchStock` carries its own `BranchScope` global scope, so a
 * branch-scoped staff member simply never sees another branch's rows here —
 * no separate `authorizeBranch()` check needed, unlike the legacy controller
 * (which took a raw branch code from the URL and had to guard it by hand).
 */
class BranchStockResource extends Resource
{
    protected static ?string $model = BranchStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $navigationLabel = 'Stok';

    protected static ?string $modelLabel = 'Stok';

    protected static ?string $pluralModelLabel = 'Stok';

    protected static ?string $slug = 'inventaris/stok';

    public static function table(Table $table): Table
    {
        return BranchStocksTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranchStocks::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\BranchStocks\Pages;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use Filament\Resources\Pages\ListRecords;

class ListBranchStocks extends ListRecords
{
    protected static string $resource = BranchStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // A stock row is only ever created through StockAdjuster/StockAllocator.
        ];
    }
}

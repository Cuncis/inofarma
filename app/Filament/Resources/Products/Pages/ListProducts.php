<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Pages\ProductImport;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCsv')
                ->label('Impor CSV')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->url(fn () => ProductImport::getUrl()),
            CreateAction::make(),
        ];
    }
}

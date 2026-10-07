<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\BranchStocks\Tables\BranchStocksTable;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * The photos are placed inside the page itself (see ProductInfolist), under
     * the product details, so they are not listed again after the page.
     * The edit page still shows them as the Gambar tab.
     */
    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Stock is kept per branch, so each button asks which branch. They use
            // the same receiving/adjusting code as Inventaris > Stok, so the stock
            // history and the permissions behave identically.
            Action::make('terimaBarang')
                ->label(__('Terima Barang'))
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->color('gray')
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Inventaris:Terima Barang'))
                ->modalHeading(fn (Product $record) => __('Terima Barang').': '.$record->name)
                ->modalDescription(__('Tambahkan batch baru ke stok produk ini. Untuk cabang yang belum punya stok produk ini, ini sekaligus menjadi stok awalnya.'))
                ->schema([
                    Select::make('branchId')
                        ->label(__('Cabang'))
                        ->options(fn () => BranchStocksTable::branchOptions())
                        ->default(fn () => Auth::guard('web')->user()?->branch_id)
                        ->searchable()
                        ->required()
                        ->rule(fn () => BranchStocksTable::mustBeOwnBranch()),
                    ...BranchStocksTable::receiveFields(),
                ])
                ->action(function (array $data, Product $record) {
                    BranchStocksTable::receiveGoods(Branch::findOrFail($data['branchId']), $record, $data);
                    $this->refreshStock();
                }),
            Action::make('sesuaikanStok')
                ->label(__('Sesuaikan Stok'))
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->color('gray')
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Inventaris:Sesuaikan Stok'))
                ->modalHeading(fn (Product $record) => __('Sesuaikan Stok').': '.$record->name)
                ->schema(fn (Product $record) => [
                    Select::make('branchId')
                        ->label(__('Cabang'))
                        ->options(BranchStocksTable::branchOptions($record, onlyWithStock: true))
                        ->default(fn () => Auth::guard('web')->user()?->branch_id)
                        ->helperText(__('Hanya cabang yang sudah punya stok produk ini. Untuk stok pertama, pakai Terima Barang.'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->rule(fn () => BranchStocksTable::mustBeOwnBranch()),
                    ...BranchStocksTable::adjustFields(
                        fn (Get $get) => $get('branchId')
                            ? BranchStock::withoutGlobalScopes()
                                ->where('branch_id', $get('branchId'))
                                ->where('product_id', $record->id)
                                ->value('quantity')
                            : null,
                    ),
                ])
                ->action(function (array $data, Product $record) {
                    BranchStocksTable::adjustStock(Branch::findOrFail($data['branchId']), $record, $data);
                    $this->refreshStock();
                }),
            EditAction::make(),
        ];
    }

    /** Re-reads the product so the "Stok per Cabang" box shows the new numbers. */
    private function refreshStock(): void
    {
        $this->record->unsetRelation('stocks');
        $this->record->refresh();
    }
}

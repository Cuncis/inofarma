<?php

namespace App\Filament\Resources\BranchStocks\Pages;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use App\Filament\Resources\BranchStocks\Tables\BranchStocksTable;
use App\Models\Branch;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListBranchStocks extends ListRecords
{
    protected static string $resource = BranchStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // A stock row only exists once goods have been received at that branch,
            // so a new product has nothing to click "Terima Barang" on. This is
            // how its first stock gets in: it creates the row and the first batch
            // through the same StockAllocator::receive() as every other receipt.
            Action::make('stokAwal')
                ->label(__('Tambah Stok Awal'))
                ->icon(Heroicon::OutlinedPlus)
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Inventaris:Terima Barang'))
                ->modalHeading(__('Tambah Stok Awal'))
                ->modalDescription(__('Pilih cabang dan produk, lalu isi batch pertama yang diterima. Cocok untuk produk baru yang belum punya stok di cabang itu.'))
                ->modalSubmitActionLabel(__('Simpan Stok'))
                ->schema([
                    Select::make('branchId')
                        ->label(__('Cabang'))
                        ->options(fn () => BranchStocksTable::branchOptions())
                        ->default(fn () => Auth::guard('web')->user()?->branch_id)
                        ->searchable()
                        ->required()
                        ->rule(fn () => BranchStocksTable::mustBeOwnBranch()),
                    Select::make('productId')
                        ->label(__('Produk'))
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => self::productOptions($search))
                        ->getOptionLabelUsing(fn ($value) => Product::find($value)?->name)
                        ->required()
                        ->helperText(__('Produk berstatus Arsip tidak muncul.')),
                    ...BranchStocksTable::receiveFields(),
                ])
                ->action(function (array $data) {
                    $branch = Branch::findOrFail($data['branchId']);
                    $product = Product::findOrFail($data['productId']);

                    BranchStocksTable::receiveGoods($branch, $product, $data);
                }),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function productOptions(string $search): array
    {
        return Product::query()
            ->where('status', '!=', 'arsip')
            ->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Product $product) => [$product->id => "{$product->sku} · {$product->name}"])
            ->all();
    }
}

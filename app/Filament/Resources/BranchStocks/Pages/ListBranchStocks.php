<?php

namespace App\Filament\Resources\BranchStocks\Pages;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use App\Filament\Resources\BranchStocks\Tables\BranchStocksTable;
use App\Models\Branch;
use App\Models\Product;
use Closure;
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
                        ->options(fn () => self::branchOptions())
                        ->default(fn () => Auth::guard('web')->user()?->branch_id)
                        ->searchable()
                        ->required()
                        ->rule(fn () => self::mustBeOwnBranch()),
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
    private static function branchOptions(): array
    {
        $branchId = Auth::guard('web')->user()?->branch_id;

        return Branch::query()
            ->when($branchId, fn ($query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
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

    /**
     * Staff tied to one branch may only book stock into that branch.
     */
    private static function mustBeOwnBranch(): Closure
    {
        return function (string $attribute, $value, Closure $fail) {
            $branchId = Auth::guard('web')->user()?->branch_id;

            if ($branchId !== null && $branchId !== (int) $value) {
                $fail(__('Anda hanya bisa menambah stok di cabang Anda sendiri.'));
            }
        };
    }
}

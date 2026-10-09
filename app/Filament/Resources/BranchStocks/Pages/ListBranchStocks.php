<?php

namespace App\Filament\Resources\BranchStocks\Pages;

use App\Filament\Resources\BranchStocks\BranchStockResource;
use App\Filament\Resources\BranchStocks\Tables\BranchStocksTable;
use App\Models\Branch;
use App\Models\Product;
use App\Support\CsvTemplates\ImportFailures;
use App\Support\CsvTemplates\StockTemplateImporter;
use App\Support\CsvTemplates\TemplateExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            Action::make('importStock')
                ->label(__('Import Stok'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Inventaris:Sesuaikan Stok'))
                ->modalHeading(__('Import Stok'))
                ->modalDescription(__('Stok yang ada diganti sesuai file: batch yang tidak ada di file menjadi 0. Produk dan cabang yang tidak ada di file tidak berubah.'))
                ->modalSubmitActionLabel(__('Import'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('File CSV'))
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->maxSize(10240)
                        ->storeFiles(false)
                        ->helperText(__('Satu baris per produk, cabang, dan batch. Unduh templatenya lewat tombol Export & Template.')),
                ])
                ->action(fn (array $data) => self::importStock($data['file'])),
            ActionGroup::make([
                Action::make('exportStock')
                    ->label(__('Export Stok'))
                    ->action(fn () => (new TemplateExporter)->stock()),
                Action::make('downloadStockTemplate')
                    ->label(__('Unduh Template Stok'))
                    ->action(fn () => (new TemplateExporter)->stockSample()),
            ])
                ->label(__('Export & Template'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->button(),
        ];
    }

    /**
     * Replaces batch stock from the uploaded CSV, as the signed-in staff member:
     * a branch-confined user can only import their own branch.
     */
    private static function importStock(TemporaryUploadedFile $file): void
    {
        $user = Auth::guard('web')->user();

        try {
            $result = (new StockTemplateImporter($user?->branch_id, $user?->id))
                ->import($file->getRealPath(), $file->getClientOriginalName());
        } catch (\RuntimeException $exception) {
            Notification::make()->danger()->title(__('Import gagal'))->body($exception->getMessage())->send();

            return;
        }

        $failures = ImportFailures::group($result['failed']);

        $notification = Notification::make()
            ->title(__('Import stok selesai: :groups produk per cabang diperbarui.', ['groups' => $result['groups']]));

        if ($failures === []) {
            $notification->success()->send();

            return;
        }

        $notification->warning()->persistent()->body(
            collect($failures)->take(5)->map(fn (array $failure) => sprintf(
                '%s (%s: %s%s)',
                $failure['message'],
                __(':count baris', ['count' => $failure['count']]),
                __('Baris :rows', ['rows' => implode(', ', $failure['rows'])]),
                $failure['more'] > 0 ? ', '.__('dan :count lainnya', ['count' => $failure['more']]) : '',
            ))->implode("\n"),
        )->send();
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

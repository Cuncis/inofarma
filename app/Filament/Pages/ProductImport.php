<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\CsvTemplates\ProductTemplateImporter;
use App\Support\CsvTemplates\StockTemplateImporter;
use App\Support\CsvTemplates\TemplateExporter;
use App\Support\ProductCsvImporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Bulk import and export of the catalogue and its stock as CSV.
 *
 * The house template is two files, `produk.csv` and `stok.csv` (see
 * ProductTemplateImporter and StockTemplateImporter); both can be exported,
 * edited and imported back, and sample templates are downloadable here. The
 * older Shopify-format import (ProductCsvImporter) stays as `import`. Not in
 * the main navigation: reached from the Produk list's "Import CSV" header
 * action.
 */
class ProductImport extends Page
{
    protected string $view = 'filament.pages.product-import';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    public static function getNavigationLabel(): string
    {
        return __('Import Produk');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Import & Export CSV');
    }

    protected static ?string $slug = 'produk/impor';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Produk:Lihat');
    }

    protected function getHeaderActions(): array
    {
        $user = fn () => Auth::guard('web')->user();

        return [
            Action::make('importProducts')
                ->label(__('Import Produk'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->visible(fn () => (bool) $user()?->can('Produk:Ubah'))
                ->schema([$this->csvUpload()])
                ->action(function (array $data) {
                    $this->runImport(function () use ($data) {
                        set_time_limit(300);

                        $result = (new ProductTemplateImporter)->import($data['file']);

                        $this->notifyDone(__('Import selesai: :created produk baru, :updated diperbarui.', ['created' => $result['created'], 'updated' => $result['updated']]));

                        return $result;
                    });
                }),
            Action::make('importStock')
                ->label(__('Import Stok'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->visible(fn () => (bool) $user()?->can('Inventaris:Sesuaikan Stok'))
                ->modalDescription(__('Stok yang ada diganti sesuai file: batch yang tidak ada di file menjadi 0. Produk dan cabang yang tidak ada di file tidak berubah.'))
                ->schema([$this->csvUpload()])
                ->action(function (array $data) use ($user) {
                    $this->runImport(function () use ($data, $user) {
                        $result = (new StockTemplateImporter($user()?->branch_id, $user()?->id))->import($data['file']);

                        $this->notifyDone(__('Import stok selesai: :groups produk per cabang diperbarui.', ['groups' => $result['groups']]));

                        return $result;
                    });
                }),
            ActionGroup::make([
                Action::make('exportProducts')
                    ->label(__('Export Produk'))
                    ->visible(fn () => (bool) $user()?->can('Produk:Lihat'))
                    ->action(fn () => (new TemplateExporter)->products()),
                Action::make('exportStock')
                    ->label(__('Export Stok'))
                    ->visible(fn () => (bool) $user()?->can('Inventaris:Lihat'))
                    ->action(fn () => (new TemplateExporter)->stock()),
                Action::make('downloadProductTemplate')
                    ->label(__('Unduh Template Produk'))
                    ->action(fn () => (new TemplateExporter)->productSample()),
                Action::make('downloadStockTemplate')
                    ->label(__('Unduh Template Stok'))
                    ->action(fn () => (new TemplateExporter)->stockSample()),
            ])
                ->label(__('Export & Template'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->button(),
            Action::make('import')
                ->label(__('Import Format Shopify'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->schema([$this->csvUpload()])
                ->action(function (array $data) {
                    set_time_limit(300);

                    $this->result = ['kind' => 'produk'] + (new ProductCsvImporter)->import($data['file']);

                    $this->notifyDone(__('Import selesai: :created produk baru, :updated diperbarui.', ['created' => $this->result['created'], 'updated' => $this->result['updated']]));
                }),
            Action::make('backToProducts')
                ->label(__('Kembali ke Produk'))
                ->color('gray')
                ->url(fn () => ProductResource::getUrl('index')),
        ];
    }

    private function csvUpload(): FileUpload
    {
        return FileUpload::make('file')
            ->label(__('File CSV'))
            ->required()
            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
            ->maxSize(10240)
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                $path = tempnam(sys_get_temp_dir(), 'produk-impor').'.csv';
                file_put_contents($path, $file->get());

                return $path;
            });
    }

    /**
     * Runs an import and keeps its summary for the page. A file the importer
     * rejects outright (empty, or missing a required column) becomes an error
     * toast instead of a server error.
     *
     * @param  \Closure(): array<string, mixed>  $import
     */
    private function runImport(\Closure $import): void
    {
        try {
            $this->result = $import();
        } catch (\RuntimeException $e) {
            Notification::make()->danger()->title(__('Import gagal'))->body($e->getMessage())->send();
        }
    }

    private function notifyDone(string $title): void
    {
        Notification::make()->success()->title($title)->send();
    }
}

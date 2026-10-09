<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\CsvTemplates\CatalogTemplateImporter;
use App\Support\CsvTemplates\ImportFailures;
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
 * The house template is one file, `produk-stok.csv`: product and stock together,
 * one row per product, branch and batch (see
 * CatalogTemplateImporter); it can be exported, edited and imported back, and a
 * sample template is downloadable here. The
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

    /**
     * The failed rows folded by message, so one cause that hits hundreds of
     * rows shows once with its row numbers.
     *
     * @return list<array{message: string, count: int, rows: list<int>, more: int}>
     */
    public function groupedFailures(): array
    {
        return ImportFailures::group($this->result['failed'] ?? []);
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Produk:Lihat');
    }

    protected function getHeaderActions(): array
    {
        $user = fn () => Auth::guard('web')->user();

        return [
            Action::make('importCatalog')
                ->label(__('Import Produk & Stok'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->visible(fn () => (bool) ($user()?->can('Produk:Ubah') || $user()?->can('Inventaris:Sesuaikan Stok')))
                ->modalDescription(__('Satu file untuk produk dan stok. Stok per produk dan cabang diganti sesuai file: batch yang tidak ada di file menjadi 0. Produk dan cabang yang tidak ada di file tidak berubah.'))
                ->schema([$this->csvUpload()])
                ->action(function (array $data) use ($user) {
                    $this->runImport(function () use ($data, $user) {
                        set_time_limit(300);

                        $result = (new CatalogTemplateImporter(
                            restrictToBranchId: $user()?->branch_id,
                            userId: $user()?->id,
                            canEditProducts: (bool) $user()?->can('Produk:Ubah'),
                            canAdjustStock: (bool) $user()?->can('Inventaris:Sesuaikan Stok'),
                        ))->import($data['file']);

                        $this->notifyDone(__('Import selesai: :created produk baru, :updated diperbarui, :groups stok produk per cabang diperbarui.', [
                            'created' => $result['created'], 'updated' => $result['updated'], 'groups' => $result['groups'],
                        ]));

                        return $result;
                    });
                }),
            ActionGroup::make([
                Action::make('exportCatalog')
                    ->label(__('Export Produk & Stok'))
                    ->visible(fn () => (bool) ($user()?->can('Produk:Lihat') || $user()?->can('Inventaris:Lihat')))
                    ->action(fn () => (new TemplateExporter)->catalog((bool) $user()?->can('Inventaris:Lihat'))),
                Action::make('downloadCatalogTemplate')
                    ->label(__('Unduh Template'))
                    ->action(fn () => (new TemplateExporter)->catalogSample()),
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

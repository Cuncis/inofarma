<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\ProductCsvImporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * One-off bulk import of products from a Shopify-format product-export CSV.
 * See ProductCsvImporter's docblock: SKU/Handle are the idempotency key, so
 * re-uploading the same export updates existing rows instead of duplicating
 * them. Not in the main navigation — reached from the Produk list's "Impor
 * CSV" header action, same as it hangs off /admin/produk in the legacy app.
 */
class ProductImport extends Page
{
    protected string $view = 'filament.pages.product-import';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Impor Produk';

    protected static ?string $title = 'Impor Produk (CSV)';

    protected static ?string $slug = 'produk/impor';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array{created: int, updated: int, warnedInactive: int, imagesFailed: list<string>, failed: list<array{row: int, message: string}>}|null */
    public ?array $result = null;

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Produk:Lihat');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Impor')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('file')
                        ->label('Berkas CSV')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->maxSize(10240)
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $path = tempnam(sys_get_temp_dir(), 'produk-impor').'.csv';
                            file_put_contents($path, $file->get());

                            return $path;
                        }),
                ])
                ->action(function (array $data) {
                    set_time_limit(300);

                    $this->result = (new ProductCsvImporter)->import($data['file']);

                    Notification::make()
                        ->success()
                        ->title("Impor selesai: {$this->result['created']} produk baru, {$this->result['updated']} diperbarui.")
                        ->send();
                }),
            Action::make('backToProducts')
                ->label('Kembali ke Produk')
                ->color('gray')
                ->url(fn () => ProductResource::getUrl('index')),
        ];
    }
}

<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\AuditLogger;
use App\Support\CodeSequence;
use App\Support\ProductImageUploader;
use App\Support\Slug;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * Photos picked on the form. They can't be stored until the product exists,
     * because each file lives in a folder named after the product's id.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    private array $pendingPhotos = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sku'] = CodeSequence::next(Product::withTrashed(), 'sku', 'PRD-');
        $data['slug'] = Slug::unique(Product::withTrashed(), $data['name']);
        // No prescription drugs are sold, so this is never anything but false.
        $data['requires_prescription'] = false;

        // `photos` is not a column on products; keep the files for afterCreate().
        $this->pendingPhotos = array_values($data['photos'] ?? []);
        unset($data['photos']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->attachPendingPhotos();

        AuditLogger::log('produk_ditambahkan', $this->record, [], $this->record->only([
            'name', 'price', 'drug_class', 'status',
        ]));
    }

    /**
     * Stores the photos picked on the form through the same uploader as the
     * Gambar tab (resize plus thumbnail). The first becomes the main photo. A
     * file that cannot be processed is reported but never undoes the product.
     */
    private function attachPendingPhotos(): void
    {
        $failed = 0;

        foreach ($this->pendingPhotos as $file) {
            try {
                $path = ProductImageUploader::store($file, $this->record->id)['path'];
            } catch (Throwable) {
                $failed++;

                continue;
            }

            $this->record->images()->create([
                'path' => $path,
                'position' => (int) $this->record->images()->max('position') + 1,
                'is_primary' => ! $this->record->images()->where('is_primary', true)->exists(),
            ]);
        }

        if ($failed > 0) {
            Notification::make()
                ->warning()
                ->title(__(':count foto gagal diproses.', ['count' => $failed]))
                ->body(__('Produk sudah disimpan. Tambahkan fotonya lagi lewat tab Gambar.'))
                ->persistent()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductImage;
use App\Support\ProductImageUploader;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Product photo upload, reordering, primary selection and removal — see
 * ProductImageController's docblock for why this is kept separate from the
 * product's own form: these are multipart requests against a product that
 * already exists, which a relation manager naturally requires too.
 */
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Gambar');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('Gambar'))
                    ->square(),
                IconColumn::make('is_primary')
                    ->label(__('Utama'))
                    ->boolean(),
                TextColumn::make('position')
                    ->label(__('Urutan')),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label(__('Upload Gambar'))
                    ->schema([
                        FileUpload::make('images')
                            ->label(__('Gambar'))
                            ->multiple()
                            ->image()
                            ->maxSize(5120)
                            ->required()
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                return ProductImageUploader::store($file, $this->getOwnerRecord()->id)['path'];
                            }),
                    ])
                    ->action(function (array $data) {
                        $record = $this->getOwnerRecord();
                        $position = (int) $record->images()->max('position') + 1;
                        $hasPrimary = $record->images()->where('is_primary', true)->exists();

                        foreach (array_values($data['images']) as $index => $path) {
                            $record->images()->create([
                                'path' => $path,
                                'position' => $position++,
                                'is_primary' => (! $hasPrimary) && $index === 0,
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('makePrimary')
                    ->label(__('Jadikan Utama'))
                    ->visible(fn (ProductImage $record) => ! $record->is_primary)
                    ->action(function (ProductImage $record) {
                        $this->getOwnerRecord()->images()->update(['is_primary' => false]);
                        $record->update(['is_primary' => true]);
                    }),
                DeleteAction::make()
                    ->before(function (ProductImage $record) {
                        // Seeded/demo images are static files under public/media,
                        // never on the uploads disk — see ProductImage's docblock.
                        if (! str_starts_with($record->path, '/media/')) {
                            ProductImageUploader::destroy($record->path);
                        }
                    })
                    ->after(function (ProductImage $record) {
                        if ($record->is_primary) {
                            $this->getOwnerRecord()->images()->orderBy('position')->first()?->update(['is_primary' => true]);
                        }
                    }),
            ]);
    }
}

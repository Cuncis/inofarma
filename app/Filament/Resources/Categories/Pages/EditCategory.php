<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Support\Slug;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in CategoriesTable.
            DeleteAction::make()
                ->before(function (Category $record, DeleteAction $action) {
                    $count = $record->products()->count();

                    if ($count > 0) {
                        Notification::make()
                            ->danger()
                            ->title("Kategori \"{$record->name}\" masih dipakai {$count} produk dan tidak bisa dihapus.")
                            ->send();

                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Slug::unique(
            Category::withTrashed(),
            $data['slug'] ?: $this->record->slug,
            'slug',
            $this->record->id,
        );

        return $data;
    }
}

<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Support\Slug;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same guard as the row-level delete in BranchesTable — a branch
            // with stock on the shelf or orders in its history can't be
            // deleted, only closed via `status`.
            DeleteAction::make()
                ->before(function (Branch $record, DeleteAction $action) {
                    $stockCount = $record->stocks()->where('quantity', '>', 0)->count();
                    $orderCount = $record->orders()->count();

                    if ($stockCount > 0 || $orderCount > 0) {
                        Notification::make()
                            ->danger()
                            ->title("\"{$record->name}\" masih punya stok atau riwayat pesanan dan tidak bisa dihapus.")
                            ->body('Ubah statusnya menjadi Tutup Permanen.')
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
        $data['slug'] = Slug::unique(Branch::withTrashed(), $data['name'], 'slug', $this->record->id);
        $data['maps_url'] = isset($data['latitude'], $data['longitude'])
            ? "https://www.google.com/maps?q={$data['latitude']},{$data['longitude']}"
            : null;

        return $data;
    }
}

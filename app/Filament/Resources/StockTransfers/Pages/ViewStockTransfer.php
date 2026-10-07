<?php

namespace App\Filament\Resources\StockTransfers\Pages;

use App\Filament\Resources\StockTransfers\StockTransferResource;
use App\Models\StockTransfer;
use App\Support\Inventory\StockTransferManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ViewStockTransfer extends ViewRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ship')
                ->label(__('Kirim'))
                ->icon(Heroicon::OutlinedTruck)
                ->visible(fn (StockTransfer $record) => $record->can_be_shipped && self::onSide($record->from_branch_id))
                ->action(function (StockTransfer $record) {
                    try {
                        $updated = app(StockTransferManager::class)->ship($record, Auth::guard('web')->id());
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title(__('Transfer :code dikirim dari :name.', ['code' => $updated->code, 'name' => $updated->fromBranch->name]))
                        ->send();
                }),
            Action::make('receive')
                ->label(__('Terima'))
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->visible(fn (StockTransfer $record) => $record->can_be_received && self::onSide($record->to_branch_id))
                ->action(function (StockTransfer $record) {
                    try {
                        $updated = app(StockTransferManager::class)->receive($record, Auth::guard('web')->id());
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title(__('Transfer :code diterima di :name.', ['code' => $updated->code, 'name' => $updated->toBranch->name]))
                        ->send();
                }),
            Action::make('cancel')
                ->label(__('Batalkan'))
                ->color('danger')
                ->icon(Heroicon::OutlinedXCircle)
                ->requiresConfirmation()
                ->visible(fn (StockTransfer $record) => $record->can_be_cancelled && self::onSide($record->from_branch_id))
                ->action(function (StockTransfer $record) {
                    try {
                        $updated = app(StockTransferManager::class)->cancel($record);
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title(__('Transfer :code dibatalkan.', ['code' => $updated->code]))
                        ->send();
                }),
        ];
    }

    /**
     * A branch-scoped staff member may only act on transfers touching their
     * own branch — mirrors StockTransferController::authorizeSide().
     */
    private static function onSide(int $branchId): bool
    {
        $user = Auth::guard('web')->user();

        return $user->branch_id === null || $user->branch_id === $branchId;
    }
}

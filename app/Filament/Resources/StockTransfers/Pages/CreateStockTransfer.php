<?php

namespace App\Filament\Resources\StockTransfers\Pages;

use App\Filament\Resources\StockTransfers\StockTransferResource;
use App\Models\StockTransfer;
use App\Support\CodeSequence;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = CodeSequence::next(StockTransfer::query(), 'code', 'TRF-');
        $data['status'] = 'diminta';
        $data['requested_by'] = Auth::guard('web')->id();

        return $data;
    }
}

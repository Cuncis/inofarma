<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\AuditLogger;
use App\Support\CodeSequence;
use App\Support\Slug;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sku'] = CodeSequence::next(Product::withTrashed(), 'sku', 'PRD-');
        $data['slug'] = Slug::unique(Product::withTrashed(), $data['name']);

        return $data;
    }

    protected function afterCreate(): void
    {
        AuditLogger::log('produk_ditambahkan', $this->record, [], $this->record->only([
            'name', 'price', 'drug_class', 'status',
        ]));
    }
}

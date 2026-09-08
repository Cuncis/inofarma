<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\AuditLogger;
use App\Support\Slug;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /** @var array<string, mixed> */
    private array $auditedBefore = [];

    private const AUDITED_FIELDS = [
        'name', 'price', 'drug_class', 'nie_bpom', 'composition', 'dosage',
        'side_effects', 'warning', 'manufacturer', 'storage', 'status',
    ];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->after(fn (Product $record) => AuditLogger::log('produk_dihapus', null, ['name' => $record->name, 'sku' => $record->sku])),
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
        $this->auditedBefore = $this->record->only(self::AUDITED_FIELDS);
        $data['slug'] = Slug::unique(Product::withTrashed(), $data['name'], 'slug', $this->record->id);

        return $data;
    }

    protected function afterSave(): void
    {
        AuditLogger::log('produk_diubah', $this->record, $this->auditedBefore, $this->record->only(self::AUDITED_FIELDS));
    }
}

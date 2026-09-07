<?php

namespace App\Filament\Resources\Attributes\Pages;

use App\Filament\Resources\Attributes\AttributeResource;
use App\Models\Attribute;
use App\Support\Slug;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Slug::unique(Attribute::withTrashed(), $data['name']);
        $data['values'] = $data['type'] === 'pilihan' ? array_values($data['values'] ?? []) : null;

        return $data;
    }
}

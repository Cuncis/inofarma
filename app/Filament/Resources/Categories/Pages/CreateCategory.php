<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Support\Slug;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Slug::unique(Category::withTrashed(), $data['slug'] ?: $data['name']);
        $data['position'] = (int) Category::max('position') + 1;
        $data['image_path'] = '/media/images/small/img-'.(Category::count() % 12 + 1).'.jpg';

        return $data;
    }
}

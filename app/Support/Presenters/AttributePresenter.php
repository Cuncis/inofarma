<?php

namespace App\Support\Presenters;

use App\Models\Attribute;
use App\Support\AdminOptions;

/**
 * `id` is the slug — `/admin/atribut/bentuk-sediaan`.
 */
class AttributePresenter
{
    /**
     * @param  iterable<Attribute>  $attributes
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $attributes): array
    {
        return collect($attributes)->map(fn (Attribute $attribute) => self::toArray($attribute))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Attribute $attribute): array
    {
        return [
            'id' => $attribute->slug,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'type' => AdminOptions::toLabel(AdminOptions::ATTRIBUTE_TYPES, $attribute->type),
            'values' => $attribute->values ?? [],
        ];
    }
}

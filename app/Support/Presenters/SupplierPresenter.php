<?php

namespace App\Support\Presenters;

use App\Models\Supplier;
use App\Support\AdminOptions;

/**
 * Turns a `Supplier` into the shape the admin "Penjual" screens expect.
 *
 * The prop names still say seller because the screens do. In a chain you own,
 * the outside party supplies you rather than sells for you, which is why the
 * table is `suppliers` — renaming the screens is a labelling decision, not a
 * data one, so it is deliberately left alone here.
 */
class SupplierPresenter
{
    /**
     * Suppliers have no logo column. The theme's logo files are assigned by
     * position so a given supplier keeps the same mark between requests.
     *
     * @var list<string>
     */
    private const LOGOS = ['nike', 'dyson', 'huawei', 'gopro', 'zara', 'rolex', 'thenorthface'];

    /**
     * @param  iterable<Supplier>  $suppliers
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $suppliers): array
    {
        return collect($suppliers)->map(fn (Supplier $supplier) => self::toArray($supplier))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Supplier $supplier): array
    {
        return [
            'id' => $supplier->code,
            'name' => $supplier->name,
            'owner' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'license' => $supplier->license_number,
            'city' => $supplier->kota,
            'address' => $supplier->address_line,
            'logo' => '/media/images/seller/'.self::LOGOS[($supplier->id - 1) % count(self::LOGOS)].'.svg',
            'status' => AdminOptions::toLabel(AdminOptions::SUPPLIER_STATUSES, $supplier->status),
            'joined' => $supplier->created_at?->translatedFormat('d M Y'),
            'products' => (int) ($supplier->products_count ?? $supplier->products()->count()),
            'revenue' => (int) ($supplier->revenue ?? 0),
        ];
    }
}

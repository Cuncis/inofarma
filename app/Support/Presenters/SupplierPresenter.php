<?php

namespace App\Support\Presenters;

use App\Models\Supplier;
use App\Support\AdminOptions;

/**
 * Turns a `Supplier` into the shape the admin "Pemasok" screens expect.
 *
 * The screens used to say "Penjual" (seller) — a marketplace word that never
 * fit a chain sourcing its own stock. Fase 4.3 renamed the label, route
 * segment and controller; this presenter's prop keys were already generic
 * (`owner`, `city`, ...), so nothing here needed to change.
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

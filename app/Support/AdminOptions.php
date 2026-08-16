<?php

namespace App\Support;

/**
 * Vocabulary shared between the admin screens and the database.
 *
 * The database stores lowercase enum values ('menunggu pembayaran'); the screens
 * show title-case Indonesian labels ('Menunggu Pembayaran'). This class is the
 * only place that knows both, so a status never gets translated by hand in a
 * controller or a Blade-style string somewhere.
 *
 * Every list here is ordered the way it should appear in a dropdown.
 */
class AdminOptions
{
    /** Editable product status. Stock-derived labels live in {@see stockLabel()}. */
    public const PRODUCT_STATUSES = [
        'Aktif' => 'aktif',
        'Nonaktif' => 'nonaktif',
        'Arsip' => 'arsip',
    ];

    public const CATEGORY_STATUSES = [
        'Aktif' => 'aktif',
        'Nonaktif' => 'nonaktif',
    ];

    public const SUPPLIER_STATUSES = [
        'Aktif' => 'aktif',
        'Nonaktif' => 'nonaktif',
    ];

    public const CUSTOMER_STATUSES = [
        'Aktif' => 'aktif',
        'Nonaktif' => 'nonaktif',
        'Diblokir' => 'diblokir',
    ];

    public const ORDER_STATUSES = [
        'Menunggu Pembayaran' => 'menunggu pembayaran',
        'Diproses' => 'diproses',
        'Siap Diambil' => 'siap diambil',
        'Dikirim' => 'dikirim',
        'Selesai' => 'selesai',
        'Dibatalkan' => 'dibatalkan',
        'Kedaluwarsa' => 'kedaluwarsa',
    ];

    /** How the customer receives the order: delivered, or collected at a branch. */
    public const FULFILMENTS = [
        'Antar' => 'antar',
        'Ambil' => 'ambil',
    ];

    /**
     * Sale units. Not an enum in the database — a pharmacy adds units over time,
     * so this is a suggestion list, validated but cheap to extend.
     *
     * @return list<string>
     */
    public static function units(): array
    {
        return ['Strip', 'Botol', 'Box', 'Tablet', 'Pcs'];
    }

    /**
     * @return list<string>
     */
    public static function paymentMethods(): array
    {
        return ['Transfer Bank', 'GoPay', 'OVO', 'DANA', 'Tunai'];
    }

    /**
     * The labels of a status map, for dropdowns and `Rule::in()`.
     *
     * @param  array<string, string>  $map
     * @return list<string>
     */
    public static function labels(array $map): array
    {
        return array_keys($map);
    }

    /**
     * Label to stored value: 'Menunggu Pembayaran' → 'menunggu pembayaran'.
     *
     * @param  array<string, string>  $map
     */
    public static function toValue(array $map, string $label): string
    {
        return $map[$label] ?? throw new \InvalidArgumentException("Status tidak dikenal: {$label}");
    }

    /**
     * Stored value back to label, for rendering.
     *
     * @param  array<string, string>  $map
     */
    public static function toLabel(array $map, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return array_search($value, $map, true) ?: null;
    }

    /**
     * How stock across every branch reads on a badge.
     *
     * Separate from the product's own status on purpose: a product can be Aktif
     * and still be out of stock everywhere, and those are different facts.
     */
    public static function stockLabel(int $quantity, int $lowThreshold = 20): string
    {
        return match (true) {
            $quantity <= 0 => 'Habis',
            $quantity <= $lowThreshold => 'Stok Menipis',
            default => 'Tersedia',
        };
    }
}

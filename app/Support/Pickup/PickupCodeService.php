<?php

namespace App\Support\Pickup;

use App\Models\Order;
use App\Models\User;
use App\Support\AuditLogger;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * The "kode ambil (angka pendek + QR)" ROADMAP.md 7.2 asks for. Issued when
 * an admin marks a pickup order `siap diambil` — not at checkout, since the
 * code means nothing until the item is actually staged at the counter — with
 * a 48-hour window (0.3's own suggested default).
 *
 * QR rendering reuses `bacon/bacon-qr-code`, already a direct dependency for
 * 2FA (`Admin\TwoFactorController`), so this needed no new package. It
 * encodes a URL into the admin's own hand-over screen rather than the bare
 * code — a phone camera without an app to type into is still useful,
 * `Admin/PickupQueue.jsx` pre-fills the code from the URL's query string,
 * and the counter staff still has to press "Serahkan" themselves; nothing
 * about scanning it hands an order over on its own.
 */
class PickupCodeService
{
    private const WINDOW_HOURS = 48;

    public static function issue(Order $order): Order
    {
        $order->update([
            'status' => 'siap diambil',
            'ready_at' => now(),
            'pickup_code' => self::generateCode(),
            'pickup_code_expires_at' => now()->addHours(self::WINDOW_HOURS),
        ]);

        return $order->fresh();
    }

    public static function qrSvgDataUri(Order $order): ?string
    {
        if (! $order->pickup_code) {
            return null;
        }

        $url = route('admin.pengambilan.index', ['order' => $order->number, 'kode' => $order->pickup_code]);
        $renderer = new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd);
        $svg = (new Writer($renderer))->writeString($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * @throws PickupCodeException when the code is wrong, expired, or the
     *                             order isn't waiting for pickup at all
     */
    public static function handOver(Order $order, string $code, User $staff): Order
    {
        if ($order->status !== 'siap diambil') {
            throw new PickupCodeException("Pesanan #{$order->number} belum siap diambil.");
        }

        if ($order->is_pickup_code_expired) {
            throw new PickupCodeException("Kode ambil pesanan #{$order->number} sudah kedaluwarsa.");
        }

        if (! $order->pickup_code || ! hash_equals($order->pickup_code, $code)) {
            throw new PickupCodeException('Kode ambil tidak cocok.');
        }

        $order->update([
            'status' => 'selesai',
            'completed_at' => now(),
            'picked_up_at' => now(),
            'handed_over_by' => $staff->id,
        ]);

        AuditLogger::log(
            'pesanan.serahkan',
            $order,
            newValues: ['picked_up_at' => now()->toIso8601String(), 'handed_over_by' => $staff->id],
            actor: $staff,
        );

        return $order->fresh();
    }

    private static function generateCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (
            Order::withoutGlobalScopes()
                ->where('pickup_code', $code)
                ->where('status', 'siap diambil')
                ->exists()
        );

        return $code;
    }
}

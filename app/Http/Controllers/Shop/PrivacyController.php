<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PDP (UU 27/2022) self-service, Fase 9.2: a customer's own copy of their
 * data, and account deletion. Both act only on `$request->user('customer')`
 * — there is no id in either route, on purpose, so nobody can export or
 * delete anyone's data but their own.
 */
class PrivacyController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Shop/PrivacyCenter');
    }

    /** A plain JSON download of everything this app holds about the signed-in customer. */
    public function export(Request $request): HttpResponse
    {
        $customer = $request->user('customer');
        $customer->load(['addresses', 'orders.items']);

        $payload = [
            'profil' => [
                'kode' => $customer->code,
                'nama' => $customer->name,
                'email' => $customer->email,
                'telepon' => $customer->phone,
                'bergabung_pada' => $customer->created_at?->toIso8601String(),
                'persetujuan_pada' => $customer->consent_at?->toIso8601String(),
                'versi_persetujuan' => $customer->consent_version,
            ],
            'alamat' => $customer->addresses->map(fn ($address) => [
                'label' => $address->label,
                'penerima' => $address->recipient_name,
                'telepon' => $address->phone,
                'alamat' => $address->address_line,
                'kota' => $address->kota,
                'provinsi' => $address->provinsi,
                'koordinat' => [$address->latitude, $address->longitude],
            ])->all(),
            'pesanan' => $customer->orders->map(fn ($order) => [
                'nomor' => $order->number,
                'tanggal' => $order->created_at?->toIso8601String(),
                'status' => $order->status,
                'total' => $order->grand_total,
                'item' => $order->items->map(fn ($item) => "{$item->product_name} x{$item->quantity}")->all(),
            ])->all(),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"data-{$customer->code}.json\"",
        ]);
    }

    /**
     * Soft-deletes the account (order history stays intact — Fase 1's own
     * rule: an order is a financial record, never rewritten) but scrambles
     * the identifying fields, clears saved addresses and the active cart,
     * and frees the email address for reuse. A real erasure, not a cosmetic one.
     */
    public function destroyAccount(Request $request): RedirectResponse
    {
        $customer = $request->user('customer');

        $request->validate(['password' => ['required', 'current_password:customer']]);

        $customer->addresses()->delete();
        $customer->cart()->delete();

        $customer->update([
            'name' => 'Pelanggan Dihapus',
            'email' => "dihapus-{$customer->id}-{$customer->email}",
            'phone' => null,
        ]);
        $customer->delete();

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Akun Anda telah dihapus.');
    }
}

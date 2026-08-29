<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Region;
use App\Support\Cart\CartManager;
use App\Support\CodeSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Checkout without an existing account. A guest fills their own details
 * (name/phone/email) and a delivery address in one form; behind the scenes
 * this silently creates a real `Customer` account from those same fields
 * (random, never-shown password — `ForgotPassword` is how they'd set a real
 * one later if they want to sign back in) and signs them in, so every bit of
 * downstream machinery — `CheckoutController`, DOKU payment, order
 * tracking, order history, the admin's own customer view — runs completely
 * unchanged for what started as a "guest" order. A signed-in customer never
 * sees this screen; `Shop/Cart.jsx`'s checkout button skips straight to
 * `ui.checkout` for them, which already prefills from their saved default
 * address (Fase 0's "boleh checkout sebagai tamu?" — this is that decision,
 * made).
 */
class GuestCheckoutController extends Controller
{
    public function create(Request $request, CartManager $cart): Response|RedirectResponse
    {
        if ($request->user('customer')) {
            return redirect()->route('ui.checkout');
        }

        $data = $cart->current();

        if (! $data['branch'] || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        return Inertia::render('Shop/GuestCheckout', [
            'provinces' => Region::query()->where('level', 1)->orderBy('name')->get(['code', 'name'])
                ->map(fn (Region $region) => ['code' => $region->code, 'name' => $region->name]),
        ]);
    }

    public function store(Request $request, CartManager $cart): RedirectResponse
    {
        $data = $cart->current();

        if (! $data['branch'] || count($data['lines']) === 0) {
            return redirect()->route('ui.cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            // Same PDP consent gate as `AuthController::register()` — a guest
            // checkout still creates an account, so it still needs it.
            'consent' => ['accepted'],
            'addressLine' => ['required', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk untuk melanjutkan.',
            'consent.accepted' => 'Anda harus menyetujui Kebijakan Privasi untuk melanjutkan.',
        ], [
            'addressLine' => 'alamat lengkap', 'kota' => 'kota/kabupaten', 'provinsi' => 'provinsi',
        ]);

        $customer = Customer::create([
            'code' => CodeSequence::next(Customer::withTrashed(), 'code', 'CUS-'),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(40)),
            'status' => 'aktif',
            'consent_at' => now(),
            'consent_version' => AuthController::CONSENT_VERSION,
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        // Same order as `AuthController::login()`: the merge needs the
        // now-authenticated guard to have already regenerated the session,
        // and `setAddress()` below needs `$cart` to see this customer as
        // signed in — both true from this point on, same request.
        $cart->mergeGuestIntoCustomer($customer);

        $address = $customer->addresses()->create([
            'label' => 'Rumah',
            'recipient_name' => $validated['name'],
            'phone' => $validated['phone'],
            'address_line' => $validated['addressLine'],
            'kelurahan' => $validated['kelurahan'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kota' => $validated['kota'],
            'provinsi' => $validated['provinsi'],
            'postal_code' => $validated['postalCode'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => true,
        ]);

        $cart->setAddress($address);

        $customer->sendEmailVerificationNotification();

        return redirect()->route('ui.checkout');
    }
}

<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Support\Cart\CartManager;
use App\Support\Presenters\CustomerAddressPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A signed-in customer's saved addresses. Every read and write is scoped to
 * `$request->user('customer')` — there is no admin-facing equivalent, and no
 * customer ever sees another customer's address by id.
 */
class AddressController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Shop/MyAddress', [
            'addresses' => CustomerAddressPresenter::collection(
                $this->customer($request)->addresses()->orderByDesc('is_default')->orderByDesc('id')->get()
            ),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Shop/AddNewAddress');
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $this->customer($request);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'recipientName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'addressLine' => ['required', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'note' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [], [
            'label' => 'label alamat', 'recipientName' => 'nama penerima', 'addressLine' => 'alamat lengkap',
            'kota' => 'kota/kabupaten', 'provinsi' => 'provinsi',
        ]);

        $customer->addresses()->create([
            'label' => $data['label'],
            'recipient_name' => $data['recipientName'],
            'phone' => $data['phone'],
            'address_line' => $data['addressLine'],
            'kelurahan' => $data['kelurahan'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kota' => $data['kota'],
            'provinsi' => $data['provinsi'],
            'postal_code' => $data['postalCode'] ?? null,
            'note' => $data['note'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_default' => $customer->addresses()->doesntExist(),
        ]);

        return redirect()->route('ui.my-address')->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function destroy(Request $request, int $address): RedirectResponse
    {
        $record = $this->address($request, $address);
        $wasDefault = $record->is_default;
        $record->delete();

        if ($wasDefault) {
            $this->customer($request)->addresses()->orderByDesc('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Alamat dihapus.');
    }

    public function makeDefault(Request $request, int $address): RedirectResponse
    {
        $customer = $this->customer($request);
        $record = $this->address($request, $address);

        DB::transaction(function () use ($customer, $record) {
            $customer->addresses()->where('id', '!=', $record->id)->update(['is_default' => false]);
            $record->update(['is_default' => true]);
        });

        return back()->with('success', 'Alamat utama diperbarui.');
    }

    /** The address picker that "Detail pengiriman" on the checkout page links out to. */
    public function forCheckout(Request $request): Response
    {
        return Inertia::render('Shop/ShippingDetails', [
            'addresses' => CustomerAddressPresenter::collection(
                $this->customer($request)->addresses()->orderByDesc('is_default')->orderByDesc('id')->get()
            ),
        ]);
    }

    public function selectForCheckout(Request $request, CartManager $cart): RedirectResponse
    {
        $data = $request->validate(['addressId' => ['required', 'integer']]);

        $cart->setAddress($this->address($request, (int) $data['addressId']));

        return redirect()->route('ui.checkout');
    }

    private function customer(Request $request): Customer
    {
        return $request->user('customer');
    }

    private function address(Request $request, int $id): CustomerAddress
    {
        return $this->customer($request)->addresses()->where('id', $id)
            ->firstOr(fn () => abort(404, 'Alamat tidak ditemukan.'));
    }
}

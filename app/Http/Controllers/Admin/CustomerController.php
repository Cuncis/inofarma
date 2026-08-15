<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Support\Catalog;
use App\Support\CustomerStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Customer CRUD for the admin.
 *
 * Order history is read-only here and belongs to the customer's email, so a
 * customer who has ordered cannot be deleted — deactivate them instead.
 */
class CustomerController extends Controller
{
    public function __construct(private readonly CustomerStore $customers) {}

    public function index(): Response
    {
        return Inertia::render('Admin/CustomerList', [
            'customers' => $this->customers->withStats(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CustomerAdd', [
            'statuses' => Catalog::customerStatuses(),
        ]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = $this->customers->create($request->validated());

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Pelanggan \"{$customer['name']}\" berhasil ditambahkan.");
    }

    public function show(string $customer): Response
    {
        $record = $this->customers->findOrFail($customer);

        return Inertia::render('Admin/CustomerDetail', [
            'customer' => $record,
            'orders' => $this->customers->ordersFor($record['email']),
        ]);
    }

    public function edit(string $customer): Response
    {
        return Inertia::render('Admin/CustomerEdit', [
            'customer' => $this->customers->findOrFail($customer),
            'statuses' => Catalog::customerStatuses(),
        ]);
    }

    public function update(CustomerRequest $request, string $customer): RedirectResponse
    {
        $updated = $this->customers->update($customer, $request->validated());

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Data \"{$updated['name']}\" berhasil diperbarui.");
    }

    public function destroy(string $customer): RedirectResponse
    {
        $record = $this->customers->findOrFail($customer);

        if (! $this->customers->delete($customer)) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', "\"{$record['name']}\" memiliki {$record['orders']} pesanan dan tidak bisa dihapus. Ubah statusnya menjadi Nonaktif.");
        }

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Pelanggan \"{$record['name']}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        $this->customers->reset();

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', 'Daftar pelanggan dikembalikan ke data awal.');
    }
}

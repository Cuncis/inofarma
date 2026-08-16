<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Support\AdminOptions;
use App\Support\CodeSequence;
use App\Support\Presenters\CustomerPresenter;
use App\Support\Presenters\OrderPresenter;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Customer CRUD for the admin.
 *
 * Order history is read-only here, and `orders.customer_id` is
 * `restrictOnDelete`, so a customer who has ordered cannot be removed —
 * deactivate them instead.
 *
 * City and address are stored on the customer's default address, not on the
 * customer row: a customer can have several addresses, and checkout needs them.
 */
class CustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/CustomerList', [
            'customers' => CustomerPresenter::collection(
                $this->withStats()->orderBy('id')->get()
            ),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CustomerAdd', [
            'statuses' => AdminOptions::labels(AdminOptions::CUSTOMER_STATUSES),
        ]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $customer = Customer::create([
            'code' => CodeSequence::next(Customer::withTrashed(), 'code', 'CUS-'),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            // Staff never choose a customer's password. A random one is set so
            // the column is filled; the customer claims the account by resetting
            // it (Fase 3.3).
            'password' => Hash::make(Str::random(40)),
            'avatar_path' => '/media/images/users/avatar-'.(Customer::count() % 12 + 1).'.jpg',
            'status' => AdminOptions::toValue(AdminOptions::CUSTOMER_STATUSES, $data['status']),
        ]);

        $this->syncAddress($customer, $data);

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Pelanggan \"{$customer->name}\" berhasil ditambahkan.");
    }

    public function show(string $customer): Response
    {
        $record = $this->find($customer);

        $orders = $record->orders()
            ->with(['items', 'customer', 'branch'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/CustomerDetail', [
            'customer' => CustomerPresenter::toArray($record),
            'orders' => OrderPresenter::collection($orders),
        ]);
    }

    public function edit(string $customer): Response
    {
        return Inertia::render('Admin/CustomerEdit', [
            'customer' => CustomerPresenter::toArray($this->find($customer)),
            'statuses' => AdminOptions::labels(AdminOptions::CUSTOMER_STATUSES),
        ]);
    }

    public function update(CustomerRequest $request, string $customer): RedirectResponse
    {
        $record = $this->find($customer);
        $data = $request->validated();

        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => AdminOptions::toValue(AdminOptions::CUSTOMER_STATUSES, $data['status']),
        ]);

        $this->syncAddress($record, $data);

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Data \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $customer): RedirectResponse
    {
        $record = $this->find($customer);
        $count = $record->orders()->count();

        if ($count > 0) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', "\"{$record->name}\" memiliki {$count} pesanan dan tidak bisa dihapus. Ubah statusnya menjadi Nonaktif.");
        }

        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', "Pelanggan \"{$name}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.pelanggan.index')
            ->with('success', 'Daftar pelanggan dikembalikan ke data awal.');
    }

    /**
     * @return Builder<Customer>
     */
    private function withStats(): Builder
    {
        return Customer::query()
            ->with('addresses')
            ->withCount('orders')
            ->withSum(
                ['orders as spent_total' => fn ($query) => $query
                    ->whereNotIn('status', ['dibatalkan', 'kedaluwarsa']),
                ],
                'grand_total',
            );
    }

    private function find(string $code): Customer
    {
        return $this->withStats()
            ->where('code', $code)
            ->firstOr(fn () => abort(404, 'Pelanggan tidak ditemukan.'));
    }

    /**
     * Keep the default address in step with what the form submitted.
     *
     * @param  array<string, mixed>  $data
     */
    private function syncAddress(Customer $customer, array $data): void
    {
        $customer->addresses()->updateOrCreate(
            ['is_default' => true],
            [
                'label' => 'Rumah',
                'recipient_name' => $data['name'],
                'phone' => $data['phone'],
                'address_line' => ($data['address'] ?? null) ?: '—',
                'kota' => $data['city'],
                'provinsi' => 'DKI Jakarta',
            ],
        );

        $customer->load('addresses');
    }
}

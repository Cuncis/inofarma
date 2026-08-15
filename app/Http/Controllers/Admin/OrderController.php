<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Support\Catalog;
use App\Support\CustomerStore;
use App\Support\OrderStore;
use App\Support\ProductStore;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Order CRUD for the admin.
 *
 * Line items are snapshotted from the catalogue at write time, so the order
 * keeps the name and price it was placed at. A completed order cannot be
 * deleted — set it to Dibatalkan instead.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderStore $orders,
        private readonly CustomerStore $customers,
        private readonly ProductStore $products,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/OrderList', [
            'orders' => $this->withCustomers($this->orders->all()),
            'statuses' => Catalog::orderStatuses(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/OrderAdd', $this->formOptions());
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $order = $this->orders->create($this->prepare($request->validated()));

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$order['id']} berhasil dibuat.");
    }

    public function show(string $order): Response
    {
        $record = $this->orders->findOrFail($order);

        return Inertia::render('Admin/OrderDetail', [
            'order' => $this->attachCustomer($record),
            'statuses' => Catalog::orderStatuses(),
        ]);
    }

    public function edit(string $order): Response
    {
        return Inertia::render('Admin/OrderEdit', [
            ...$this->formOptions(),
            'order' => $this->orders->findOrFail($order),
        ]);
    }

    public function update(OrderRequest $request, string $order): RedirectResponse
    {
        $updated = $this->orders->update($order, $this->prepare($request->validated()));

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$updated['id']} berhasil diperbarui.");
    }

    public function destroy(string $order): RedirectResponse
    {
        $record = $this->orders->findOrFail($order);

        if (! $this->orders->delete($order)) {
            return redirect()
                ->route('admin.pesanan.index')
                ->with('error', "Pesanan #{$record['id']} sudah selesai dan tidak bisa dihapus. Ubah statusnya menjadi Dibatalkan bila perlu.");
        }

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$record['id']} berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        $this->orders->reset();

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', 'Daftar pesanan dikembalikan ke data awal.');
    }

    /**
     * Snapshot each line's product name and price at write time.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data): array
    {
        $catalogue = collect($this->products->all())->keyBy('id');

        $data['items'] = array_map(function (array $item) use ($catalogue) {
            $product = $catalogue->get($item['productId']);

            return [
                'productId' => $item['productId'],
                'name' => $product['name'],
                'price' => $product['price'],
                'qty' => (int) $item['qty'],
            ];
        }, $data['items']);

        return $data;
    }

    /**
     * @param  list<array<string, mixed>>  $orders
     * @return list<array<string, mixed>>
     */
    private function withCustomers(array $orders): array
    {
        return array_map(fn (array $order) => $this->attachCustomer($order), $orders);
    }

    /**
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private function attachCustomer(array $order): array
    {
        $customer = collect($this->customers->all())
            ->firstWhere('email', $order['customerEmail']);

        $order['customerName'] = $customer['name'] ?? $order['customerEmail'];
        $order['customerId'] = $customer['id'] ?? null;
        $order['customerAvatar'] = $customer['avatar'] ?? null;

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'customers' => array_map(
                fn (array $customer) => ['email' => $customer['email'], 'name' => $customer['name']],
                $this->customers->all(),
            ),
            'products' => array_map(
                fn (array $product) => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                ],
                $this->products->all(),
            ),
            'statuses' => Catalog::orderStatuses(),
            'payments' => Catalog::paymentMethods(),
        ];
    }
}

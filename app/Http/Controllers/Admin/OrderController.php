<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\CodeSequence;
use App\Support\Pickup\PickupCodeService;
use App\Support\Presenters\CustomerPresenter;
use App\Support\Presenters\OrderPresenter;
use App\Support\Presenters\ProductPresenter;
use App\Support\Shipping\ShipmentService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Order CRUD for the admin.
 *
 * Line items are snapshotted from the catalogue at write time — name, SKU and
 * unit price are copied onto the row, so repricing or deleting a product later
 * cannot rewrite what somebody was charged. The totals are stored for the same
 * reason rather than recomputed on read.
 *
 * A completed order cannot be deleted; set it to Dibatalkan instead.
 */
class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/OrderList', [
            'orders' => OrderPresenter::collection($this->query()->get()),
            'statuses' => AdminOptions::labels(AdminOptions::ORDER_STATUSES),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/OrderAdd', $this->formOptions());
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $order = Order::create($this->attributes($request->validated()) + [
                'number' => CodeSequence::next(Order::withTrashed(), 'number', 'INO-', 0, 2450),
            ]);

            return $this->syncItems($order, $request->validated()['items']);
        });

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$order->number} berhasil dibuat.");
    }

    public function show(string $order): Response
    {
        return Inertia::render('Admin/OrderDetail', [
            'order' => OrderPresenter::toArray($this->find($order)),
            'statuses' => AdminOptions::labels(AdminOptions::ORDER_STATUSES),
        ]);
    }

    public function edit(string $order): Response
    {
        return Inertia::render('Admin/OrderEdit', [
            ...$this->formOptions(),
            'order' => OrderPresenter::toArray($this->find($order)),
        ]);
    }

    public function update(OrderRequest $request, string $order): RedirectResponse
    {
        $record = $this->find($order);

        DB::transaction(function () use ($record, $request) {
            $record->update($this->attributes($request->validated()));
            $this->syncItems($record, $request->validated()['items']);
        });

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$record->number} berhasil diperbarui.");
    }

    public function destroy(string $order): RedirectResponse
    {
        $record = $this->find($order);

        if (! $record->is_deletable) {
            return redirect()
                ->route('admin.pesanan.index')
                ->with('error', "Pesanan #{$record->number} sudah selesai dan tidak bisa dihapus. Ubah statusnya menjadi Dibatalkan bila perlu.");
        }

        $number = $record->number;
        $record->delete();

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$number} berhasil dihapus.");
    }

    /** Books the real Biteship waybill for this order's already-quoted courier (Fase 7.1). */
    public function ship(string $order): RedirectResponse
    {
        $record = $this->find($order);

        try {
            ShipmentService::make()->bookForOrder($record);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Resi untuk pesanan #{$record->number} berhasil dibuat.");
    }

    /** Issues the pickup code + QR and moves the order to "siap diambil" (Fase 7.2). */
    public function markReady(string $order): RedirectResponse
    {
        $record = $this->find($order);

        if ($record->fulfilment !== 'ambil') {
            return back()->with('error', 'Hanya pesanan Ambil di Toko yang punya kode ambil.');
        }

        $record = PickupCodeService::issue($record);

        return back()->with('success', "Pesanan #{$record->number} siap diambil. Kode: {$record->pickup_code}.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.pesanan.index')
            ->with('success', 'Daftar pesanan dikembalikan ke data awal.');
    }

    /**
     * @return Builder<Order>
     */
    private function query(): Builder
    {
        return Order::query()
            ->with(['items', 'customer', 'branch'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function find(string $number): Order
    {
        return $this->query()
            ->where('number', $number)
            ->firstOr(fn () => abort(404, 'Pesanan tidak ditemukan.'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $status = AdminOptions::toValue(AdminOptions::ORDER_STATUSES, $data['status']);

        return [
            'customer_id' => Customer::where('email', $data['customerEmail'])->value('id'),
            'branch_id' => Branch::where('code', $data['branch'])->value('id'),
            'fulfilment' => AdminOptions::toValue(AdminOptions::FULFILMENTS, $data['fulfilment']),
            'status' => $status,
            'payment_method' => $data['payment'],
            'payment_status' => $status === 'selesai' ? 'lunas' : 'belum bayar',
            'shipping_total' => $data['shipping'],
            'note' => $data['note'] ?? '',
        ];
    }

    /**
     * Replace the order's lines and recompute its money columns.
     *
     * @param  list<array{productId: string, qty: int|string}>  $items
     */
    private function syncItems(Order $order, array $items): Order
    {
        $products = Product::whereIn('sku', array_column($items, 'productId'))->get()->keyBy('sku');

        $order->items()->delete();

        foreach ($items as $item) {
            $product = $products[$item['productId']];
            $quantity = (int) $item['qty'];

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => $product->price * $quantity,
            ]);
        }

        $subtotal = (int) $order->items()->sum('line_total');

        $order->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $order->shipping_total,
        ]);

        return $order->load('items');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'customers' => CustomerPresenter::options(Customer::orderBy('id')->get()),
            'products' => ProductPresenter::options(Product::orderBy('id')->get()),
            'branches' => Branch::orderBy('id')->get()
                ->map(fn (Branch $branch) => ['id' => $branch->code, 'name' => $branch->name])
                ->all(),
            'statuses' => AdminOptions::labels(AdminOptions::ORDER_STATUSES),
            'payments' => AdminOptions::paymentMethods(),
            'fulfilments' => AdminOptions::labels(AdminOptions::FULFILMENTS),
        ];
    }
}

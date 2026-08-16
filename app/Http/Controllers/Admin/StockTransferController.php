<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockTransferRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Support\AdminOptions;
use App\Support\Inventory\StockTransferManager;
use App\Support\Presenters\BranchPresenter;
use App\Support\Presenters\ProductPresenter;
use App\Support\Presenters\StockTransferPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Moving stock between branches. See `App\Support\Inventory\StockTransferManager`
 * for the diminta → dikirim → diterima workflow this drives.
 */
class StockTransferController extends Controller
{
    public function __construct(private readonly StockTransferManager $transfers) {}

    public function index(): Response
    {
        $transfers = StockTransfer::query()
            ->with(['fromBranch', 'toBranch', 'product', 'requestedBy'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/StockTransferList', [
            'transfers' => StockTransferPresenter::collection($transfers),
            'statuses' => AdminOptions::labels(AdminOptions::STOCK_TRANSFER_STATUSES),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StockTransferAdd', [
            'branches' => BranchPresenter::options(Branch::active()->orderBy('name')->get()),
            'products' => ProductPresenter::options(Product::orderBy('name')->get()),
        ]);
    }

    public function store(StockTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $transfer = $this->transfers->request([
            'from_branch_id' => Branch::where('code', $data['fromBranch'])->value('id'),
            'to_branch_id' => Branch::where('code', $data['toBranch'])->value('id'),
            'product_id' => Product::where('sku', $data['product'])->value('id'),
            'quantity' => $data['quantity'],
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.inventaris.transfer.index')
            ->with('success', "Permintaan transfer {$transfer->code} dibuat.");
    }

    public function show(string $transfer): Response
    {
        return Inertia::render('Admin/StockTransferDetail', [
            'transfer' => StockTransferPresenter::toArray($this->find($transfer)),
        ]);
    }

    public function ship(string $transfer): RedirectResponse
    {
        try {
            $record = $this->transfers->ship($this->find($transfer));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} dikirim dari {$record->fromBranch->name}.");
    }

    public function receive(string $transfer): RedirectResponse
    {
        try {
            $record = $this->transfers->receive($this->find($transfer));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} diterima di {$record->toBranch->name}.");
    }

    public function cancel(string $transfer): RedirectResponse
    {
        try {
            $record = $this->transfers->cancel($this->find($transfer));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} dibatalkan.");
    }

    private function find(string $code): StockTransfer
    {
        return StockTransfer::with(['fromBranch', 'toBranch', 'product', 'requestedBy'])
            ->where('code', $code)
            ->firstOr(fn () => abort(404, 'Transfer tidak ditemukan.'));
    }
}

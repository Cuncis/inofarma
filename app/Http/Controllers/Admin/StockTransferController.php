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
use Illuminate\Support\Facades\Auth;
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
        $fromBranchId = Branch::where('code', $data['fromBranch'])->value('id');

        $user = Auth::guard('web')->user();
        abort_if($user->branch_id !== null && $user->branch_id !== $fromBranchId, 403, 'Anda hanya bisa meminta transfer dari cabang Anda sendiri.');

        $transfer = $this->transfers->request([
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => Branch::where('code', $data['toBranch'])->value('id'),
            'product_id' => Product::where('sku', $data['product'])->value('id'),
            'quantity' => $data['quantity'],
            'note' => $data['note'] ?? null,
        ], $user->id);

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
        $found = $this->find($transfer);
        $this->authorizeSide($found->from_branch_id, 'Hanya cabang asal yang bisa mengirim transfer ini.');

        try {
            $record = $this->transfers->ship($found, Auth::guard('web')->id());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} dikirim dari {$record->fromBranch->name}.");
    }

    public function receive(string $transfer): RedirectResponse
    {
        $found = $this->find($transfer);
        $this->authorizeSide($found->to_branch_id, 'Hanya cabang tujuan yang bisa menerima transfer ini.');

        try {
            $record = $this->transfers->receive($found, Auth::guard('web')->id());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} diterima di {$record->toBranch->name}.");
    }

    public function cancel(string $transfer): RedirectResponse
    {
        $found = $this->find($transfer);
        $this->authorizeSide($found->from_branch_id, 'Hanya cabang asal yang bisa membatalkan transfer ini.');

        try {
            $record = $this->transfers->cancel($found);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Transfer {$record->code} dibatalkan.");
    }

    private function authorizeSide(int $branchId, string $message): void
    {
        $user = Auth::guard('web')->user();

        abort_if($user->branch_id !== null && $user->branch_id !== $branchId, 403, $message);
    }

    private function find(string $code): StockTransfer
    {
        return StockTransfer::with(['fromBranch', 'toBranch', 'product', 'requestedBy'])
            ->where('code', $code)
            ->firstOr(fn () => abort(404, 'Transfer tidak ditemukan.'));
    }
}

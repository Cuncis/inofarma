<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Requests\StockReceiptRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAdjuster;
use App\Support\Inventory\StockAllocator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Per-branch stock: view what one branch is holding, correct it, or receive
 * new stock into it. The cross-branch overview (Fase 2.4's "matrix") is a
 * separate screen — {@see StockMatrixController} — because a matrix reads
 * every branch at once and a branch's own stock page reads one at a time; the
 * queries and the amount of detail they need are different enough to keep apart.
 */
class BranchStockController extends Controller
{
    public function index(): Response
    {
        $branches = Branch::query()
            ->withCount('stocks')
            ->withSum(
                ['stocks as low_stock_count' => fn ($query) => $query
                    ->whereColumn('quantity', '<=', 'reorder_point')],
                'quantity',
            )
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/BranchStockList', [
            'branches' => $branches->map(fn (Branch $branch) => [
                'id' => $branch->code,
                'name' => $branch->name,
                'kota' => $branch->kota,
                'products' => $branch->stocks_count,
                'lowStock' => BranchStock::where('branch_id', $branch->id)
                    ->whereColumn('quantity', '<=', 'reorder_point')
                    ->count(),
                'status' => AdminOptions::toLabel(AdminOptions::BRANCH_STATUSES, $branch->status),
            ])->all(),
        ]);
    }

    public function show(string $branch): Response
    {
        $record = $this->findBranch($branch);
        $this->authorizeBranch($record);

        $stocks = BranchStock::query()
            ->with('product')
            ->where('branch_id', $record->id)
            ->whereHas('product')
            ->get()
            ->sortBy(fn (BranchStock $stock) => $stock->product->name)
            ->values();

        return Inertia::render('Admin/BranchStockDetail', [
            'branch' => ['id' => $record->code, 'name' => $record->name],
            'stocks' => $stocks->map(fn (BranchStock $stock) => [
                'productId' => $stock->product->sku,
                'productName' => $stock->product->name,
                'quantity' => $stock->quantity,
                'reserved' => $stock->reserved_quantity,
                'available' => $stock->available,
                'reorderPoint' => $stock->reorder_point,
                'isLow' => $stock->is_low,
            ])->all(),
            'reasons' => AdminOptions::labels(AdminOptions::ADJUSTMENT_REASONS),
        ]);
    }

    public function adjust(StockAdjustmentRequest $request, string $branch, string $product): RedirectResponse
    {
        $branchModel = $this->findBranch($branch);
        $this->authorizeBranch($branchModel);
        $productModel = $this->findProduct($product);
        $data = $request->validated();

        try {
            (new StockAdjuster)->adjust(
                $branchModel,
                $productModel,
                (int) $data['delta'],
                AdminOptions::toValue(AdminOptions::ADJUSTMENT_REASONS, $data['reason']),
                Auth::guard('web')->id(),
                $data['note'] ?? null,
            );
        } catch (InsufficientStockException $exception) {
            return redirect()
                ->route('admin.inventaris.stok.show', $branch)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.inventaris.stok.show', $branch)
            ->with('success', "Stok \"{$productModel->name}\" di {$branchModel->name} disesuaikan.");
    }

    public function receive(StockReceiptRequest $request, string $branch, string $product): RedirectResponse
    {
        $branchModel = $this->findBranch($branch);
        $this->authorizeBranch($branchModel);
        $productModel = $this->findProduct($product);
        $data = $request->validated();

        (new StockAllocator)->receive(
            $branchModel,
            $productModel,
            [[
                'batch_number' => $data['batchNumber'],
                'expires_at' => $data['expiresAt'],
                'quantity' => $data['quantity'],
                'cost_price' => $data['costPrice'] ?? null,
            ]],
            'pembelian',
            null,
            Auth::guard('web')->id(),
            $data['note'] ?? null,
        );

        return redirect()
            ->route('admin.inventaris.stok.show', $branch)
            ->with('success', "{$data['quantity']} \"{$productModel->name}\" diterima di {$branchModel->name}.");
    }

    private function findBranch(string $code): Branch
    {
        return Branch::where('code', $code)
            ->firstOr(fn () => abort(404, 'Cabang tidak ditemukan.'));
    }

    /**
     * A branch-scoped staff member (Fase 3.2) may only touch their own
     * branch's stock — `Branch` itself carries no query scope, so this is the
     * one place left to check before `StockAdjuster`/`StockAllocator` run,
     * since those deliberately bypass `BranchScope` (see their docblocks).
     */
    private function authorizeBranch(Branch $branch): void
    {
        $user = Auth::guard('web')->user();

        abort_if($user->branch_id !== null && $user->branch_id !== $branch->id, 403, 'Bukan cabang Anda.');
    }

    private function findProduct(string $sku): Product
    {
        return Product::where('sku', $sku)
            ->firstOr(fn () => abort(404, 'Produk tidak ditemukan.'));
    }
}

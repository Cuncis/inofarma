<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One product × every branch, at a glance.
 *
 * Read-only by design — adjusting a number here would hide which branch and
 * which reason a change belongs to. Corrections happen on the branch's own
 * stock page ({@see BranchStockController}), which asks for both.
 */
class StockMatrixController extends Controller
{
    public function index(): Response
    {
        $branches = Branch::query()->active()->orderBy('name')->get(['id', 'code', 'name']);

        $products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'category_id']);

        $quantities = BranchStock::query()
            ->whereIn('branch_id', $branches->pluck('id'))
            ->get(['branch_id', 'product_id', 'quantity', 'reorder_point'])
            ->groupBy('product_id');

        return Inertia::render('Admin/StockMatrix', [
            'branches' => $branches->map(fn (Branch $branch) => [
                'id' => $branch->code,
                'name' => $branch->name,
            ])->all(),
            'rows' => $products->map(function (Product $product) use ($branches, $quantities) {
                $stocksByBranch = ($quantities->get($product->id) ?? collect())->keyBy('branch_id');

                return [
                    'productId' => $product->sku,
                    'productName' => $product->name,
                    'category' => $product->category?->name,
                    'cells' => $branches->map(function (Branch $branch) use ($stocksByBranch) {
                        $stock = $stocksByBranch->get($branch->id);

                        return [
                            'quantity' => $stock->quantity ?? 0,
                            'isLow' => $stock && $stock->reorder_point > 0
                                && $stock->quantity <= $stock->reorder_point,
                        ];
                    })->all(),
                ];
            })->all(),
        ]);
    }
}

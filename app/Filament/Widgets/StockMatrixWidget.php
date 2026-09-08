<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

/**
 * One product per row, one column per active branch — read-only by design,
 * see StockMatrixController's docblock. Columns are generated per branch at
 * render time since Filament tables have a fixed schema and the number of
 * branches isn't known statically.
 */
class StockMatrixWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $branches = Branch::query()->active()->orderBy('name')->get(['id', 'code', 'name']);

        return $table
            ->heading('Matriks Stok')
            ->records(fn (): Collection => $this->rows($branches))
            ->columns([
                TextColumn::make('productName')
                    ->label('Produk'),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->placeholder('—'),
                ...$branches->map(fn (Branch $branch) => TextColumn::make("branch_{$branch->id}")
                    ->label($branch->name)
                    ->alignRight()
                    ->badge()
                    ->color(fn (mixed $state) => (is_array($state) && ($state['isLow'] ?? false)) ? 'danger' : 'gray')
                    ->formatStateUsing(fn (mixed $state) => is_array($state) ? ($state['quantity'] ?? 0) : $state))->all(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada produk.');
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @return Collection<int, array<string, mixed>>
     */
    private function rows(Collection $branches): Collection
    {
        $products = Product::query()->with('category')->orderBy('name')->get(['id', 'sku', 'name', 'category_id']);

        $quantities = BranchStock::query()
            ->whereIn('branch_id', $branches->pluck('id'))
            ->get(['branch_id', 'product_id', 'quantity', 'reorder_point'])
            ->groupBy('product_id');

        return $products->mapWithKeys(function (Product $product) use ($branches, $quantities) {
            $stocksByBranch = ($quantities->get($product->id) ?? collect())->keyBy('branch_id');

            $row = [
                'productName' => $product->name,
                'category' => $product->category?->name,
            ];

            foreach ($branches as $branch) {
                $stock = $stocksByBranch->get($branch->id);

                $row["branch_{$branch->id}"] = [
                    'quantity' => $stock->quantity ?? 0,
                    'isLow' => $stock && $stock->reorder_point > 0 && $stock->quantity <= $stock->reorder_point,
                ];
            }

            return [$product->sku => $row];
        });
    }
}

<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAdjuster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjusterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_positive_delta_increases_the_count(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);

        $stock = (new StockAdjuster)->adjust($branch, $product, 5, 'penyesuaian');

        $this->assertSame(15, $stock->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $branch->id, 'product_id' => $product->id,
            'type' => 'penyesuaian', 'quantity' => 5, 'balance_after' => 15,
        ]);
    }

    public function test_a_negative_delta_decreases_the_count(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);

        $stock = (new StockAdjuster)->adjust($branch, $product, -3, 'rusak');

        $this->assertSame(7, $stock->quantity);
    }

    public function test_cannot_adjust_below_zero(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 4]);

        $this->expectException(InsufficientStockException::class);

        (new StockAdjuster)->adjust($branch, $product, -5, 'rusak');
    }

    public function test_adjusting_a_product_never_stocked_here_creates_the_row_when_positive(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        $stock = (new StockAdjuster)->adjust($branch, $product, 20, 'penyesuaian');

        $this->assertSame(20, $stock->quantity);
        $this->assertDatabaseHas('branch_stocks', [
            'branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 20,
        ]);
    }

    public function test_adjusting_a_product_never_stocked_here_fails_when_negative(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        $this->expectException(InsufficientStockException::class);

        (new StockAdjuster)->adjust($branch, $product, -1, 'rusak');
    }
}

<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Support\Inventory\StockReplacer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockReplacerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listed_batches_get_the_counted_quantity_and_unlisted_ones_go_to_zero(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 50, 'reserved_quantity' => 0]);
        $kept = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'A1', 'quantity' => 30]);
        $dropped = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'B2', 'quantity' => 20]);

        $result = (new StockReplacer)->replace($branch, $product, [
            ['batch_number' => 'A1', 'expires_at' => '2028-01-31', 'quantity' => 45],
            ['batch_number' => 'C3', 'expires_at' => '2028-06-30', 'quantity' => 10],
        ], note: 'opname');

        $this->assertSame(['batches' => 2, 'zeroed' => 1], $result);
        $this->assertSame(45, $kept->fresh()->quantity);
        $this->assertSame('2028-01-31', $kept->fresh()->expires_at->toDateString());
        $this->assertSame(0, $dropped->fresh()->quantity);
        $this->assertSame(10, InventoryBatch::where('batch_number', 'C3')->value('quantity'));
        $this->assertSame(55, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_every_change_is_a_penyesuaian_movement_that_ends_on_the_new_total(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 30]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'A1', 'quantity' => 30]);

        (new StockReplacer)->replace($branch, $product, [
            ['batch_number' => 'A1', 'expires_at' => '2028-01-31', 'quantity' => 30],
            ['batch_number' => 'N1', 'expires_at' => '2028-01-31', 'quantity' => 12],
        ], userId: null, note: 'opname');

        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $branch->id, 'product_id' => $product->id, 'type' => 'penyesuaian',
            'quantity' => 12, 'balance_after' => 42, 'note' => 'opname',
        ]);
    }

    public function test_batch_numbers_match_regardless_of_case_instead_of_duplicating(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'Bn100', 'quantity' => 5]);

        (new StockReplacer)->replace($branch, $product, [
            ['batch_number' => 'BN100', 'expires_at' => '2028-01-31', 'quantity' => 9],
        ]);

        $this->assertSame(1, InventoryBatch::where('product_id', $product->id)->count());
        $this->assertSame(9, InventoryBatch::where('product_id', $product->id)->value('quantity'));
    }

    public function test_settings_are_applied_and_reserved_stock_is_preserved(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10, 'reserved_quantity' => 4]);

        (new StockReplacer)->replace($branch, $product, [
            ['batch_number' => 'A1', 'expires_at' => '2028-01-31', 'quantity' => 8],
        ], ['reorder_point' => 25, 'price_override' => 7000, 'is_listed' => false]);

        $stock = BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->first();
        $this->assertSame(8, $stock->quantity);
        $this->assertSame(4, $stock->reserved_quantity);
        $this->assertSame(25, $stock->reorder_point);
        $this->assertSame(7000, $stock->price_override);
        $this->assertFalse($stock->is_listed);
    }

    public function test_a_count_below_the_reserved_quantity_is_refused_and_changes_nothing(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10, 'reserved_quantity' => 6]);
        $batch = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'A1', 'quantity' => 10]);

        try {
            (new StockReplacer)->replace($branch, $product, [
                ['batch_number' => 'A1', 'expires_at' => '2028-01-31', 'quantity' => 3],
            ]);
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('dipesan', $e->getMessage());
        }

        $this->assertSame(10, $batch->fresh()->quantity);
        $this->assertDatabaseCount('inventory_movements', 0);
    }
}

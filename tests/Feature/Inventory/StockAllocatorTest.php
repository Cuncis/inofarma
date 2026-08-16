<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAllocatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_consume_takes_from_the_batch_that_expires_soonest_first(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 30]);

        $soon = InventoryBatch::factory()->for($branch)->for($product)->create([
            'batch_number' => 'SOON', 'quantity' => 10, 'expires_at' => now()->addDays(30),
        ]);
        $later = InventoryBatch::factory()->for($branch)->for($product)->create([
            'batch_number' => 'LATER', 'quantity' => 20, 'expires_at' => now()->addDays(90),
        ]);

        $manifest = (new StockAllocator)->consume($branch, $product, 15, 'penjualan');

        $this->assertSame('SOON', $manifest[0]['batch_number']);
        $this->assertSame(10, $manifest[0]['quantity']);
        $this->assertSame('LATER', $manifest[1]['batch_number']);
        $this->assertSame(5, $manifest[1]['quantity']);

        $this->assertSame(0, $soon->fresh()->quantity);
        $this->assertSame(15, $later->fresh()->quantity);
        $this->assertSame(15, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_consume_writes_one_movement_per_batch_touched(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 20]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 5, 'expires_at' => now()->addDays(10)]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 15, 'expires_at' => now()->addDays(20)]);

        (new StockAllocator)->consume($branch, $product, 12, 'penjualan');

        $movements = InventoryMovement::where('branch_id', $branch->id)->where('product_id', $product->id)->get();

        $this->assertCount(2, $movements);
        $this->assertSame(-5, $movements[0]->quantity);
        $this->assertSame(-7, $movements[1]->quantity);
        $this->assertSame(15, $movements[0]->balance_after);
        $this->assertSame(8, $movements[1]->balance_after);
    }

    public function test_consume_refuses_to_go_below_zero(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 5]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 5]);

        $this->expectException(InsufficientStockException::class);

        (new StockAllocator)->consume($branch, $product, 10, 'penjualan');
    }

    public function test_reserved_quantity_is_not_available_to_consume(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($branch)->for($product)->create([
            'quantity' => 10, 'reserved_quantity' => 8,
        ]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10]);

        $this->expectException(InsufficientStockException::class);

        (new StockAllocator)->consume($branch, $product, 5, 'penjualan');
    }

    public function test_consuming_with_no_stock_row_at_all_is_insufficient(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        try {
            (new StockAllocator)->consume($branch, $product, 1, 'penjualan');
            $this->fail('Expected InsufficientStockException.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame(1, $exception->requested);
            $this->assertSame(0, $exception->available);
        }
    }

    public function test_receive_creates_a_batch_and_adds_to_the_branchs_total(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        $stock = (new StockAllocator)->receive($branch, $product, [
            ['batch_number' => 'B001', 'expires_at' => now()->addYear()->toDateString(), 'quantity' => 40],
        ], 'pembelian');

        $this->assertSame(40, $stock->quantity);
        $this->assertDatabaseHas('inventory_batches', [
            'branch_id' => $branch->id, 'product_id' => $product->id,
            'batch_number' => 'B001', 'quantity' => 40,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $branch->id, 'product_id' => $product->id,
            'type' => 'pembelian', 'quantity' => 40, 'balance_after' => 40,
        ]);
    }

    public function test_receiving_the_same_batch_number_again_extends_it_rather_than_duplicating(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();
        $expires = now()->addYear()->toDateString();

        $allocator = new StockAllocator;
        $allocator->receive($branch, $product, [
            ['batch_number' => 'B001', 'expires_at' => $expires, 'quantity' => 10],
        ], 'pembelian');
        $stock = $allocator->receive($branch, $product, [
            ['batch_number' => 'B001', 'expires_at' => $expires, 'quantity' => 15],
        ], 'pembelian');

        $this->assertSame(25, $stock->quantity);
        $this->assertSame(1, InventoryBatch::where('batch_number', 'B001')->count());
        $this->assertSame(25, InventoryBatch::where('batch_number', 'B001')->value('quantity'));
    }
}

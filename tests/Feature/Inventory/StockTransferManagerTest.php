<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockTransferManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockTransferManagerTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): StockTransferManager
    {
        return app(StockTransferManager::class);
    }

    public function test_a_request_starts_as_diminta_with_a_generated_code(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id,
            'to_branch_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertSame('diminta', $transfer->status);
        $this->assertSame('TRF-001', $transfer->code);
    }

    public function test_shipping_moves_stock_out_of_the_origin_using_fefo(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 50]);
        InventoryBatch::factory()->for($from)->for($product)->create([
            'batch_number' => 'SOON', 'quantity' => 20, 'expires_at' => now()->addDays(10),
        ]);
        InventoryBatch::factory()->for($from)->for($product)->create([
            'batch_number' => 'LATER', 'quantity' => 30, 'expires_at' => now()->addDays(60),
        ]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 25,
        ]);

        $shipped = $this->manager()->ship($transfer);

        $this->assertSame('dikirim', $shipped->status);
        $this->assertNotNull($shipped->shipped_at);
        $this->assertSame(25, BranchStock::where('branch_id', $from->id)->value('quantity'));

        // FEFO: the soon-to-expire batch went first, entirely, before touching later.
        $this->assertSame(0, InventoryBatch::where('batch_number', 'SOON')->value('quantity'));
        $this->assertSame(25, InventoryBatch::where('batch_number', 'LATER')->value('quantity'));

        // Manifest carries the batch numbers and expiry dates onward.
        $this->assertCount(2, $shipped->batches_shipped);
        $this->assertSame('SOON', $shipped->batches_shipped[0]['batch_number']);

        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $from->id, 'type' => 'transfer keluar',
            'reference_type' => StockTransfer::class, 'reference_id' => $transfer->id,
        ]);
    }

    public function test_receiving_recreates_the_same_batches_at_the_destination(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();
        $expires = now()->addDays(45)->toDateString();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 30]);
        InventoryBatch::factory()->for($from)->for($product)->create([
            'batch_number' => 'B777', 'quantity' => 30, 'expires_at' => $expires,
        ]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 12,
        ]);
        $this->manager()->ship($transfer);
        $received = $this->manager()->receive($transfer);

        $this->assertSame('diterima', $received->status);
        $this->assertNotNull($received->received_at);

        $this->assertSame(12, BranchStock::where('branch_id', $to->id)->where('product_id', $product->id)->value('quantity'));

        $destinationBatch = InventoryBatch::where('branch_id', $to->id)
            ->where('product_id', $product->id)
            ->where('batch_number', 'B777')
            ->first();

        $this->assertNotNull($destinationBatch, 'The origin batch number should exist at the destination too.');
        $this->assertSame(12, $destinationBatch->quantity);
        $this->assertSame($expires, $destinationBatch->expires_at->toDateString());

        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $to->id, 'type' => 'transfer masuk',
            'reference_type' => StockTransfer::class, 'reference_id' => $transfer->id,
        ]);
    }

    public function test_stock_is_in_transit_between_shipping_and_receiving(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 20]);
        InventoryBatch::factory()->for($from)->for($product)->create(['quantity' => 20, 'expires_at' => now()->addMonth()]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 8,
        ]);
        $this->manager()->ship($transfer);

        // Gone from the origin, not yet counted anywhere at the destination.
        $this->assertSame(12, BranchStock::where('branch_id', $from->id)->value('quantity'));
        $this->assertDatabaseMissing('branch_stocks', ['branch_id' => $to->id, 'product_id' => $product->id]);
    }

    public function test_a_request_can_be_cancelled_before_it_ships(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 5,
        ]);

        $cancelled = $this->manager()->cancel($transfer);

        $this->assertSame('dibatalkan', $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    public function test_a_shipped_transfer_cannot_be_cancelled(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($from)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addMonth()]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 5,
        ]);
        $this->manager()->ship($transfer);

        $this->expectException(RuntimeException::class);

        $this->manager()->cancel($transfer);
    }

    public function test_a_transfer_cannot_be_received_twice(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($from)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addMonth()]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 5,
        ]);
        $this->manager()->ship($transfer);
        $this->manager()->receive($transfer);

        $this->expectException(RuntimeException::class);

        $this->manager()->receive($transfer);
    }

    public function test_shipping_more_than_the_origin_has_is_refused(): void
    {
        $from = Branch::factory()->create();
        $to = Branch::factory()->create();
        $product = Product::factory()->create();

        BranchStock::factory()->for($from)->for($product)->create(['quantity' => 3]);
        InventoryBatch::factory()->for($from)->for($product)->create(['quantity' => 3, 'expires_at' => now()->addMonth()]);

        $transfer = $this->manager()->request([
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id,
            'product_id' => $product->id, 'quantity' => 9,
        ]);

        $this->expectException(InsufficientStockException::class);

        $this->manager()->ship($transfer);
    }
}

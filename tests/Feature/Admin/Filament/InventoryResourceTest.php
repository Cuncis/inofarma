<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\BranchStocks\Pages\ListBranchStocks;
use App\Filament\Resources\StockTransfers\Pages\CreateStockTransfer;
use App\Filament\Resources\StockTransfers\Pages\ListStockTransfers;
use App\Filament\Resources\StockTransfers\Pages\ViewStockTransfer;
use App\Filament\Widgets\StockMatrixWidget;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * Same scenarios as `BranchStockTest`, `StockMatrixTest` and
 * `StockTransferCrudTest`, exercised through the Filament resources/widget
 * at /admin/beta instead of the legacy Inertia routes.
 */
class InventoryResourceTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_stock_list_shows_every_seeded_row(): void
    {
        $stock = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->firstOrFail();

        Livewire::test(ListBranchStocks::class)
            ->assertCanSeeTableRecords([$stock])
            ->assertCountTableRecords(BranchStock::whereHas('product')->count());
    }

    public function test_stock_can_be_adjusted_up(): void
    {
        $stock = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->firstOrFail();
        $before = $stock->quantity;

        Livewire::test(ListBranchStocks::class)
            ->callTableAction('sesuaikan', $stock, data: [
                'delta' => 20,
                'reason' => 'penyesuaian',
                'note' => 'Hasil hitung ulang.',
            ]);

        $this->assertSame($before + 20, $stock->fresh()->quantity);
    }

    public function test_stock_cannot_be_adjusted_below_zero(): void
    {
        $stock = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->firstOrFail();
        $before = $stock->quantity;

        Livewire::test(ListBranchStocks::class)
            ->callTableAction('sesuaikan', $stock, data: [
                'delta' => -100000,
                'reason' => 'rusak',
            ]);

        $this->assertSame($before, $stock->fresh()->quantity);
    }

    public function test_adjustment_requires_a_nonzero_delta(): void
    {
        $stock = BranchStock::whereHas('product')->firstOrFail();

        Livewire::test(ListBranchStocks::class)
            ->mountTableAction('sesuaikan', $stock)
            ->setTableActionData(['delta' => 0, 'reason' => 'penyesuaian'])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['delta' => 'not_in']);
    }

    public function test_receiving_stock_creates_a_new_batch_with_an_expiry(): void
    {
        $stock = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->firstOrFail();

        Livewire::test(ListBranchStocks::class)
            ->callTableAction('terima', $stock, data: [
                'batchNumber' => 'B-NEW-001',
                'expiresAt' => now()->addYear()->toDateString(),
                'quantity' => 100,
                'costPrice' => 8000,
            ]);

        $this->assertDatabaseHas('inventory_batches', [
            'batch_number' => 'B-NEW-001', 'quantity' => 100, 'cost_price' => 8000,
        ]);
    }

    public function test_receiving_requires_a_future_expiry_date(): void
    {
        $stock = BranchStock::whereHas('product')->firstOrFail();

        Livewire::test(ListBranchStocks::class)
            ->mountTableAction('terima', $stock)
            ->setTableActionData([
                'batchNumber' => 'B-EXPIRED',
                'expiresAt' => now()->subDay()->toDateString(),
                'quantity' => 10,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['expiresAt' => 'after']);
    }

    public function test_the_matrix_has_one_row_per_product_with_a_column_per_active_branch(): void
    {
        $product = Product::orderBy('name')->firstOrFail();
        $branch = Branch::active()->orderBy('name')->firstOrFail();

        Livewire::test(StockMatrixWidget::class)
            ->assertSee($product->name)
            ->assertSee($branch->name);
    }

    public function test_a_transfer_can_be_requested(): void
    {
        $from = Branch::where('code', 'CB-001')->firstOrFail();
        $to = Branch::where('code', 'CB-002')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'product_id' => $product->id,
                'quantity' => 5,
                'note' => 'Permintaan cabang.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transfer = StockTransfer::firstOrFail();
        $this->assertSame('diminta', $transfer->status);
        $this->assertSame($from->id, $transfer->from_branch_id);
        $this->assertSame($to->id, $transfer->to_branch_id);

        Livewire::test(ListStockTransfers::class)
            ->assertCanSeeTableRecords([$transfer]);
    }

    public function test_the_origin_and_destination_must_differ(): void
    {
        $branch = Branch::where('code', 'CB-001')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $branch->id,
                'to_branch_id' => $branch->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['to_branch_id' => 'different']);
    }

    public function test_requesting_more_than_the_origin_holds_is_refused(): void
    {
        $from = Branch::where('code', 'CB-001')->firstOrFail();
        $to = Branch::where('code', 'CB-002')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'product_id' => $product->id,
                'quantity' => 100000,
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity']);
    }

    public function test_a_full_lifecycle_moves_stock_between_branches(): void
    {
        $from = Branch::where('code', 'CB-001')->firstOrFail();
        $to = Branch::where('code', 'CB-002')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        $originStock = BranchStock::where('branch_id', $from->id)->where('product_id', $product->id)->firstOrFail();
        $destinationStock = BranchStock::where('branch_id', $to->id)->where('product_id', $product->id)->firstOrFail();
        $originBefore = $originStock->quantity;
        $destinationBefore = $destinationStock->quantity;

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transfer = StockTransfer::firstOrFail();

        Livewire::test(ViewStockTransfer::class, ['record' => $transfer->getKey()])
            ->assertActionVisible('ship')
            ->callAction('ship');

        $this->assertSame('dikirim', $transfer->fresh()->status);

        Livewire::test(ViewStockTransfer::class, ['record' => $transfer->getKey()])
            ->assertActionVisible('receive')
            ->callAction('receive');

        $transfer->refresh();
        $this->assertSame('diterima', $transfer->status);

        $this->assertSame($originBefore - 5, $originStock->fresh()->quantity);
        $this->assertSame($destinationBefore + 5, $destinationStock->fresh()->quantity);
    }

    public function test_a_pending_request_can_be_cancelled(): void
    {
        $from = Branch::where('code', 'CB-001')->firstOrFail();
        $to = Branch::where('code', 'CB-002')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->call('create');

        $transfer = StockTransfer::firstOrFail();

        Livewire::test(ViewStockTransfer::class, ['record' => $transfer->getKey()])
            ->assertActionVisible('cancel')
            ->callAction('cancel');

        $this->assertSame('dibatalkan', $transfer->fresh()->status);
    }

    public function test_a_shipped_transfer_cannot_be_cancelled(): void
    {
        $from = Branch::where('code', 'CB-001')->firstOrFail();
        $to = Branch::where('code', 'CB-002')->firstOrFail();
        $product = Product::where('sku', 'PRD-001')->firstOrFail();

        Livewire::test(CreateStockTransfer::class)
            ->fillForm([
                'from_branch_id' => $from->id,
                'to_branch_id' => $to->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->call('create');

        $transfer = StockTransfer::firstOrFail();

        Livewire::test(ViewStockTransfer::class, ['record' => $transfer->getKey()])
            ->callAction('ship');

        Livewire::test(ViewStockTransfer::class, ['record' => $transfer->getKey()])
            ->assertActionHidden('cancel');
    }
}

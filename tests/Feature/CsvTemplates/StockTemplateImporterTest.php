<?php

namespace Tests\Feature\CsvTemplates;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Support\CsvTemplates\StockTemplateImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockTemplateImporterTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER = ['sku', 'branch_code', 'batch_number', 'expires_at', 'quantity'];

    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $rows
     */
    private function writeCsv(array $header, array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($path, 'w');
        fputcsv($handle, $header, escape: '\\');

        foreach ($rows as $row) {
            fputcsv($handle, $row, escape: '\\');
        }

        fclose($handle);

        return $path;
    }

    private function quantityAt(Branch $branch, Product $product): int
    {
        return (int) BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity');
    }

    public function test_rows_replace_the_quantities_and_zero_batches_missing_from_the_file(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-700']);
        $product = Product::factory()->create(['sku' => 'STK-1']);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 50]);
        $old = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'OLD', 'quantity' => 30]);
        $kept = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'KEEP', 'quantity' => 20]);

        $summary = (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [
            ['STK-1', 'CB-700', 'KEEP', '2028-03-31', '7'],
            ['STK-1', 'CB-700', 'NEW', '2029-03-31', '100'],
        ]));

        $this->assertSame([], $summary['failed']);
        $this->assertSame(1, $summary['groups']);
        $this->assertSame(2, $summary['batches']);
        $this->assertSame(1, $summary['zeroed']);
        $this->assertSame(0, $old->fresh()->quantity);
        $this->assertSame(7, $kept->fresh()->quantity);
        $this->assertSame(107, $this->quantityAt($branch, $product));
    }

    public function test_importing_the_same_file_twice_changes_nothing_the_second_time(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-701']);
        Product::factory()->create(['sku' => 'STK-2']);
        $path = $this->writeCsv(self::HEADER, [['STK-2', 'CB-701', 'B1', '2028-03-31', '40']]);

        (new StockTemplateImporter)->import($path);
        $movements = InventoryMovement::count();
        (new StockTemplateImporter)->import($path);

        $this->assertSame($movements, InventoryMovement::count());
        $this->assertSame(40, InventoryBatch::where('branch_id', $branch->id)->value('quantity'));
    }

    public function test_products_and_branches_not_in_the_file_are_untouched(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-702']);
        $other = Branch::factory()->create(['code' => 'CB-703']);
        $listed = Product::factory()->create(['sku' => 'STK-3']);
        $unlisted = Product::factory()->create(['sku' => 'STK-4']);
        BranchStock::factory()->for($other)->for($listed)->create(['quantity' => 15]);
        BranchStock::factory()->for($branch)->for($unlisted)->create(['quantity' => 25]);

        (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [['STK-3', 'CB-702', 'B1', '2028-03-31', '5']]));

        $this->assertSame(15, $this->quantityAt($other, $listed));
        $this->assertSame(25, $this->quantityAt($branch, $unlisted));
    }

    public function test_one_bad_row_skips_the_whole_product_and_branch_group(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-704']);
        $product = Product::factory()->create(['sku' => 'STK-5']);
        $good = Product::factory()->create(['sku' => 'STK-6']);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 30]);
        $batch = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'A1', 'quantity' => 30]);

        $summary = (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [
            ['STK-5', 'CB-704', 'A1', '2028-03-31', '10'],
            ['STK-5', 'CB-704', 'A2', 'bukan-tanggal', '10'],
            ['STK-6', 'CB-704', 'G1', '2028-03-31', '8'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertSame([3, 2], array_column($summary['failed'], 'row'));
        $this->assertSame(30, $batch->fresh()->quantity);
        $this->assertSame(30, $this->quantityAt($branch, $product));
        $this->assertSame(8, $this->quantityAt($branch, $good));
    }

    public function test_unknown_sku_unknown_branch_and_duplicate_batch_are_row_errors(): void
    {
        Branch::factory()->create(['code' => 'CB-705']);
        Product::factory()->create(['sku' => 'STK-7']);

        $summary = (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [
            ['NOPE', 'CB-705', 'B1', '2028-03-31', '1'],
            ['STK-7', 'CB-999', 'B1', '2028-03-31', '1'],
            ['STK-7', 'CB-705', 'B1', '2028-03-31', '1'],
            ['STK-7', 'CB-705', 'b1', '2028-03-31', '2'],
        ]));

        $this->assertSame(0, $summary['groups']);
        $messages = implode(' | ', array_column($summary['failed'], 'message'));
        $this->assertStringContainsString('NOPE', $messages);
        $this->assertStringContainsString('CB-999', $messages);
        $this->assertStringContainsString('lebih dari sekali', $messages);
        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_dates_may_be_written_day_first_and_negative_or_decimal_quantities_are_rejected(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-706']);
        Product::factory()->create(['sku' => 'STK-8']);
        Product::factory()->create(['sku' => 'STK-9']);

        $summary = (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [
            ['STK-8', 'CB-706', 'B1', '30/06/2027', '5'],
            ['STK-9', 'CB-706', 'B1', '2027-06-30', '-3'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertCount(2, $summary['failed']);
        $this->assertSame('2027-06-30', InventoryBatch::where('branch_id', $branch->id)->first()->expires_at->toDateString());
    }

    public function test_branch_level_settings_are_applied_and_cost_and_received_date_are_saved(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-707']);
        $product = Product::factory()->create(['sku' => 'STK-10']);

        (new StockTemplateImporter)->import($this->writeCsv(
            [...self::HEADER, 'batch_cost_price', 'received_at', 'reorder_point', 'price_override', 'is_listed'],
            [
                ['STK-10', 'CB-707', 'B1', '2028-03-31', '10', '3500', '2026-09-01', '', '', ''],
                ['STK-10', 'CB-707', 'B2', '2028-09-30', '10', '3600', '', '40', '8000', '0'],
            ],
        ));

        $stock = BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->first();
        $this->assertSame(40, $stock->reorder_point);
        $this->assertSame(8000, $stock->price_override);
        $this->assertFalse($stock->is_listed);

        $batch = InventoryBatch::where('batch_number', 'B1')->first();
        $this->assertSame(3500, $batch->cost_price);
        $this->assertSame('2026-09-01', $batch->received_at->toDateString());
    }

    public function test_a_branch_confined_importer_refuses_other_branches(): void
    {
        $own = Branch::factory()->create(['code' => 'CB-708']);
        Branch::factory()->create(['code' => 'CB-709']);
        Product::factory()->create(['sku' => 'STK-11']);

        $summary = (new StockTemplateImporter(restrictToBranchId: $own->id))->import($this->writeCsv(self::HEADER, [
            ['STK-11', 'CB-708', 'B1', '2028-03-31', '5'],
            ['STK-11', 'CB-709', 'B1', '2028-03-31', '5'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertStringContainsString('CB-709', $summary['failed'][0]['message']);
        $this->assertDatabaseCount('inventory_batches', 1);
    }

    public function test_a_group_below_the_reserved_quantity_is_reported_and_left_alone(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-710']);
        $product = Product::factory()->create(['sku' => 'STK-12']);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10, 'reserved_quantity' => 6]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'A1', 'quantity' => 10]);

        $summary = (new StockTemplateImporter)->import($this->writeCsv(self::HEADER, [['STK-12', 'CB-710', 'A1', '2028-03-31', '2']]));

        $this->assertSame(0, $summary['groups']);
        $this->assertStringContainsString('dipesan', $summary['failed'][0]['message']);
        $this->assertSame(10, $this->quantityAt($branch, $product));
    }

    public function test_a_file_missing_a_required_column_is_rejected_outright(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expires_at');

        (new StockTemplateImporter)->import($this->writeCsv(['sku', 'branch_code', 'batch_number', 'quantity'], [['A', 'B', 'C', '1']]));
    }
}

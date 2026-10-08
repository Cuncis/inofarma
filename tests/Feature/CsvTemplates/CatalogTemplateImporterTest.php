<?php

namespace Tests\Feature\CsvTemplates;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Support\CsvTemplates\CatalogTemplateImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CatalogTemplateImporterTest extends TestCase
{
    use RefreshDatabase;

    private const PRODUCT = ['name', 'category', 'unit', 'price', 'drug_class', 'weight_grams'];

    private const STOCK = ['branch', 'batch_number', 'expires_at', 'quantity'];

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

    public function test_one_file_creates_the_product_and_its_stock_in_several_branches(): void
    {
        $kalisari = Branch::factory()->create(['code' => 'CB-900', 'name' => 'Apotek Inofarma Kalisari']);
        $kayuManis = Branch::factory()->create(['code' => 'CB-901', 'name' => 'Apotek Inofarma Kayu Manis']);

        $path = $this->writeCsv(['sku', ...self::PRODUCT, ...self::STOCK], [
            ['CAT-1', 'Paracetamol', 'Obat Bebas', 'Strip', '5000', 'bebas', '20', 'CB-900', 'B1', '2028-01-31', '100'],
            ['CAT-1', '', '', '', '', '', '', 'CB-900', 'B2', '2028-06-30', '50'],
            ['CAT-1', '', '', '', '', '', '', 'Apotek Inofarma Kayu Manis', 'B1', '2028-01-31', '30'],
        ]);

        $summary = (new CatalogTemplateImporter)->import($path);

        $this->assertSame([], $summary['failed']);
        $this->assertSame(1, $summary['created']);
        $this->assertSame(2, $summary['groups']);

        $product = Product::where('sku', 'CAT-1')->firstOrFail();
        $this->assertSame('Paracetamol', $product->name);
        $this->assertSame(150, $this->quantityAt($kalisari, $product));
        $this->assertSame(30, $this->quantityAt($kayuManis, $product));
    }

    public function test_an_existing_product_needs_only_sku_and_stock_columns(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-902']);
        $product = Product::factory()->create(['sku' => 'CAT-2', 'name' => 'Nama Tetap', 'price' => 7000]);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 9]);
        $old = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'OLD', 'quantity' => 9]);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(
            ['sku', ...self::STOCK],
            [['CAT-2', 'CB-902', 'NEW', '2028-01-31', '40']],
        ));

        $this->assertSame([], $summary['failed']);
        $this->assertSame(0, $summary['created'] + $summary['updated']);
        $this->assertSame(1, $summary['groups']);
        $this->assertSame(1, $summary['zeroed']);
        $this->assertSame(0, $old->fresh()->quantity);
        $this->assertSame(40, $this->quantityAt($branch, $product));
        $this->assertSame('Nama Tetap', $product->fresh()->name);
        $this->assertSame(7000, $product->fresh()->price);
    }

    public function test_a_new_product_without_its_required_product_columns_is_rejected_with_its_stock(): void
    {
        Branch::factory()->create(['code' => 'CB-903']);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(
            ['sku', ...self::STOCK],
            [['CAT-3', 'CB-903', 'B1', '2028-01-31', '10']],
        ));

        $this->assertDatabaseMissing('products', ['sku' => 'CAT-3']);
        $this->assertDatabaseCount('inventory_batches', 0);
        $this->assertCount(2, $summary['failed']);
        $this->assertStringContainsString('wajib', $summary['failed'][0]['message']);
    }

    public function test_product_columns_that_disagree_between_rows_reject_the_whole_sku(): void
    {
        Branch::factory()->create(['code' => 'CB-904']);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(['sku', ...self::PRODUCT, ...self::STOCK], [
            ['CAT-4', 'Nama A', 'Obat Bebas', 'Strip', '5000', 'bebas', '20', 'CB-904', 'B1', '2028-01-31', '5'],
            ['CAT-4', 'Nama B', 'Obat Bebas', 'Strip', '5000', 'bebas', '20', 'CB-904', 'B2', '2028-01-31', '5'],
        ]));

        $this->assertDatabaseMissing('products', ['sku' => 'CAT-4']);
        $this->assertStringContainsString('name berbeda', $summary['failed'][0]['message']);
        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_product_only_rows_touch_no_stock(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-905']);
        $product = Product::factory()->create(['sku' => 'CAT-5']);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 12]);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(
            ['sku', ...self::PRODUCT, ...self::STOCK],
            [['CAT-5', 'Nama Baru', 'Obat Bebas', 'Strip', '6000', 'bebas', '20', '', '', '', '']],
        ));

        $this->assertSame(1, $summary['updated']);
        $this->assertSame(0, $summary['groups']);
        $this->assertSame(12, $this->quantityAt($branch, $product));
    }

    public function test_one_bad_stock_row_skips_that_branch_group_but_not_the_other_branch(): void
    {
        $a = Branch::factory()->create(['code' => 'CB-906']);
        $b = Branch::factory()->create(['code' => 'CB-907']);
        $product = Product::factory()->create(['sku' => 'CAT-6']);
        InventoryBatch::factory()->for($a)->for($product)->create(['batch_number' => 'KEEP', 'quantity' => 30]);
        BranchStock::factory()->for($a)->for($product)->create(['quantity' => 30]);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(['sku', ...self::STOCK], [
            ['CAT-6', 'CB-906', 'A1', '2028-01-31', '10'],
            ['CAT-6', 'CB-906', 'A2', 'salah', '10'],
            ['CAT-6', 'CB-907', 'B1', '2028-01-31', '7'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertSame(30, $this->quantityAt($a, $product));
        $this->assertSame(7, $this->quantityAt($b, $product));
    }

    public function test_a_branch_written_as_name_and_as_code_is_one_group(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-908', 'name' => 'Apotek Inofarma Jengki']);
        $product = Product::factory()->create(['sku' => 'CAT-7']);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(['sku', ...self::STOCK], [
            ['CAT-7', 'CB-908', 'B1', '2028-01-31', '10'],
            ['CAT-7', 'Apotek Inofarma Jengki', 'B2', '2028-01-31', '5'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertSame(15, $this->quantityAt($branch, $product));
    }

    public function test_an_unknown_branch_is_a_row_error_and_nothing_is_replaced(): void
    {
        Product::factory()->create(['sku' => 'CAT-8']);

        $summary = (new CatalogTemplateImporter)->import($this->writeCsv(
            ['sku', ...self::STOCK],
            [['CAT-8', 'Cabang Hantu', 'B1', '2028-01-31', '10']],
        ));

        $this->assertSame(0, $summary['groups']);
        $this->assertStringContainsString('Cabang Hantu', $summary['failed'][0]['message']);
    }

    public function test_a_stock_only_user_cannot_edit_products_and_a_catalogue_only_user_cannot_touch_stock(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-909']);
        $product = Product::factory()->create(['sku' => 'CAT-9', 'name' => 'Nama Lama']);

        $stockOnly = (new CatalogTemplateImporter(canEditProducts: false))->import($this->writeCsv(
            ['sku', 'name', ...self::STOCK],
            [['CAT-9', 'Nama Curang', 'CB-909', 'B1', '2028-01-31', '10']],
        ));

        $this->assertSame('Nama Lama', $product->fresh()->name);
        $this->assertSame(0, $this->quantityAt($branch, $product));
        $this->assertStringContainsString('izin', $stockOnly['failed'][0]['message']);

        $catalogueOnly = (new CatalogTemplateImporter(canAdjustStock: false))->import($this->writeCsv(
            ['sku', ...self::STOCK],
            [['CAT-9', 'CB-909', 'B1', '2028-01-31', '10']],
        ));

        $this->assertSame(0, $this->quantityAt($branch, $product));
        $this->assertStringContainsString('izin', $catalogueOnly['failed'][0]['message']);
    }

    public function test_a_branch_confined_user_cannot_import_another_branch(): void
    {
        $own = Branch::factory()->create(['code' => 'CB-910']);
        Branch::factory()->create(['code' => 'CB-911']);
        Product::factory()->create(['sku' => 'CAT-10']);

        $summary = (new CatalogTemplateImporter(restrictToBranchId: $own->id))->import($this->writeCsv(['sku', ...self::STOCK], [
            ['CAT-10', 'CB-910', 'B1', '2028-01-31', '5'],
            ['CAT-10', 'CB-911', 'B1', '2028-01-31', '5'],
        ]));

        $this->assertSame(1, $summary['groups']);
        $this->assertDatabaseCount('inventory_batches', 1);
    }

    public function test_a_file_without_a_sku_column_is_rejected_outright(): void
    {
        $this->expectException(RuntimeException::class);

        (new CatalogTemplateImporter)->import($this->writeCsv(['name'], [['x']]));
    }
}

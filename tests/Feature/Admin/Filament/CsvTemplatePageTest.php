<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\ProductImport;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Support\CsvTemplates\CatalogTemplateImporter;
use App\Support\CsvTemplates\ProductTemplateImporter;
use App\Support\CsvTemplates\TemplateExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class CsvTemplatePageTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    private function upload(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('data.csv', $content);
    }

    private function streamed(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    public function test_the_import_action_creates_a_product_and_its_stock_and_shows_the_summary(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-800']);
        $csv = "sku,name,category,unit,price,drug_class,weight_grams,branch,batch_number,expires_at,quantity\n"
            ."TPL-001,Produk Template,Kesehatan,Pcs,12000,bebas,30,CB-800,NEW1,2028-05-31,64\n";

        $page = Livewire::test(ProductImport::class)
            ->callAction('importCatalog', data: ['file' => $this->upload($csv)])
            ->assertHasNoActionErrors();

        $this->assertSame('produk-stok', $page->instance()->result['kind']);
        $this->assertSame(1, $page->instance()->result['created']);
        $this->assertSame(1, $page->instance()->result['groups']);

        $product = Product::where('sku', 'TPL-001')->firstOrFail();
        $this->assertSame(64, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_the_import_action_replaces_existing_batches(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-801']);
        $product = Product::factory()->create(['sku' => 'TPL-STK', 'supplier_id' => null]);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 9]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'OLD', 'quantity' => 9]);

        $csv = "sku,branch,batch_number,expires_at,quantity\nTPL-STK,CB-801,NEW1,2028-05-31,64\n";

        $page = Livewire::test(ProductImport::class)
            ->callAction('importCatalog', data: ['file' => $this->upload($csv)]);

        $this->assertSame(1, $page->instance()->result['zeroed']);
        $this->assertDatabaseHas('inventory_movements', ['type' => 'penyesuaian', 'quantity' => -9]);
    }

    public function test_the_same_failure_on_many_rows_is_shown_once_with_its_row_numbers(): void
    {
        $product = Product::factory()->create(['sku' => 'TPL-GRP', 'supplier_id' => null]);
        $lines = collect(range(1, 14))->map(fn (int $i) => "TPL-GRP,Cabang Hantu,B{$i},2028-05-31,5")->implode("\n");
        $csv = "sku,branch,batch_number,expires_at,quantity\n{$lines}\n";

        $page = Livewire::test(ProductImport::class)
            ->callAction('importCatalog', data: ['file' => $this->upload($csv)])
            ->assertSee('Cabang Hantu')
            ->assertSee('14 baris');

        $groups = $page->instance()->groupedFailures();

        $this->assertCount(1, $groups);
        $this->assertSame(14, $groups[0]['count']);
        $this->assertSame(range(2, 11), $groups[0]['rows']);
        $this->assertSame(4, $groups[0]['more']);
        $this->assertCount(14, $page->instance()->result['failed']);
        $this->assertNotNull($product);
    }

    public function test_a_file_without_a_sku_column_shows_an_error_instead_of_crashing(): void
    {
        $page = Livewire::test(ProductImport::class)
            ->callAction('importCatalog', data: ['file' => $this->upload("name,quantity\nA,1\n")]);

        $page->assertNotified(__('Import gagal'));
        $this->assertNull($page->instance()->result);
    }

    public function test_the_template_downloads_with_the_documented_columns_and_imports_cleanly(): void
    {
        Livewire::test(ProductImport::class)
            ->callAction('downloadCatalogTemplate')
            ->assertFileDownloaded('template-produk-stok.csv');

        $sample = $this->streamed((new TemplateExporter)->catalogSample());
        $this->assertStringStartsWith("\xEF\xBB\xBFsku,name,slug,category,", $sample);
        $this->assertStringContainsString(',branch,batch_number,expires_at,quantity,', $sample);

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $sample);

        $summary = (new CatalogTemplateImporter)->import($path);

        $this->assertSame(2, $summary['created'] + $summary['updated']);
        $this->assertSame(2, $summary['groups']);
        $this->assertSame([], $summary['failed']);
    }

    public function test_the_combined_export_imports_straight_back_without_changing_stock(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-802']);
        $stocked = Product::factory()->create(['sku' => 'TPL-EXP', 'supplier_id' => null]);
        Product::factory()->create(['sku' => 'TPL-NOSTOCK', 'supplier_id' => null]);
        BranchStock::factory()->for($branch)->for($stocked)->create(['quantity' => 17 + 5]);
        InventoryBatch::factory()->for($branch)->for($stocked)->create(['batch_number' => 'EXP1', 'quantity' => 17]);
        InventoryBatch::factory()->for($branch)->for($stocked)->create(['batch_number' => 'EXP2', 'quantity' => 5]);
        InventoryBatch::factory()->for($branch)->for($stocked)->create(['batch_number' => 'EMPTY', 'quantity' => 0]);

        $csv = $this->streamed((new TemplateExporter)->catalog());

        $this->assertStringContainsString('TPL-EXP', $csv);
        $this->assertStringContainsString('CB-802', $csv);
        $this->assertStringNotContainsString('EMPTY', $csv);

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csv);

        $summary = (new CatalogTemplateImporter)->import($path);

        $this->assertSame([], $summary['failed']);
        $this->assertSame(0, $summary['created']);
        $this->assertSame(0, $summary['zeroed']);
        $this->assertSame(22, BranchStock::where('branch_id', $branch->id)->where('product_id', $stocked->id)->value('quantity'));
    }

    public function test_the_export_leaves_stock_cells_empty_without_stock_permission(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-803']);
        $product = Product::factory()->create(['sku' => 'TPL-HID', 'supplier_id' => null]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'SECRET', 'quantity' => 8]);

        $csv = $this->streamed((new TemplateExporter)->catalog(includeStock: false));

        $this->assertStringContainsString('TPL-HID', $csv);
        $this->assertStringNotContainsString('SECRET', $csv);
    }

    public function test_the_product_export_imports_straight_back_without_errors(): void
    {
        Product::factory()->count(3)->create(['supplier_id' => null]);

        $csv = $this->streamed((new TemplateExporter)->products());
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csv);

        $summary = (new ProductTemplateImporter)->import($path);

        $this->assertSame([], $summary['failed']);
        $this->assertSame(0, $summary['created']);
        $this->assertSame(Product::count(), $summary['updated']);
    }
}

<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Pages\ProductImport;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
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

    public function test_the_product_import_action_creates_products_and_shows_the_summary(): void
    {
        $csv = "sku,name,category,unit,price,drug_class,weight_grams\nTPL-001,Produk Template,Kesehatan,Pcs,12000,bebas,30\n";

        $page = Livewire::test(ProductImport::class)
            ->callAction('importProducts', data: ['file' => $this->upload($csv)])
            ->assertHasNoActionErrors();

        $this->assertSame(1, $page->instance()->result['created']);
        $this->assertDatabaseHas('products', ['sku' => 'TPL-001', 'price' => 12000]);
    }

    public function test_the_stock_import_action_replaces_batches_for_the_branch(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-800']);
        $product = Product::factory()->create(['sku' => 'TPL-STK']);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 9]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'OLD', 'quantity' => 9]);

        $csv = "sku,branch_code,batch_number,expires_at,quantity\nTPL-STK,CB-800,NEW1,2028-05-31,64\n";

        $page = Livewire::test(ProductImport::class)
            ->callAction('importStock', data: ['file' => $this->upload($csv)])
            ->assertHasNoActionErrors();

        $this->assertSame('stok', $page->instance()->result['kind']);
        $this->assertSame(1, $page->instance()->result['zeroed']);
        $this->assertSame(64, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertDatabaseHas('inventory_movements', ['type' => 'penyesuaian', 'quantity' => -9]);
    }

    public function test_a_file_missing_required_columns_shows_an_error_instead_of_crashing(): void
    {
        $page = Livewire::test(ProductImport::class)
            ->callAction('importStock', data: ['file' => $this->upload("sku,quantity\nA,1\n")]);

        $page->assertNotified(__('Import gagal'));
        $this->assertNull($page->instance()->result);
    }

    public function test_the_templates_download_with_the_documented_columns(): void
    {
        Livewire::test(ProductImport::class)
            ->callAction('downloadProductTemplate')
            ->assertFileDownloaded('template-produk.csv');

        Livewire::test(ProductImport::class)
            ->callAction('downloadStockTemplate')
            ->assertFileDownloaded('template-stok.csv');

        $stockSample = $this->streamed((new TemplateExporter)->stockSample());
        $this->assertStringStartsWith("\xEF\xBB\xBFsku,branch_code,batch_number,expires_at,quantity,", $stockSample);
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

    public function test_the_stock_export_lists_batches_with_branch_codes(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-801']);
        $product = Product::factory()->create(['sku' => 'TPL-EXP']);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'EXP1', 'quantity' => 17]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'EMPTY', 'quantity' => 0]);

        $csv = $this->streamed((new TemplateExporter)->stock());

        $this->assertStringContainsString('TPL-EXP,CB-801,EXP1,', $csv);
        $this->assertStringContainsString(',17,', $csv);
        $this->assertStringNotContainsString('EMPTY', $csv);
    }
}

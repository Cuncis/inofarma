<?php

namespace Tests\Feature\CsvTemplates;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\CsvTemplates\ProductTemplateImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProductTemplateImporterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $rows
     */
    private function writeCsv(array $header, array $rows, string $delimiter = ','): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($path, 'w');
        fputcsv($handle, $header, $delimiter, escape: '\\');

        foreach ($rows as $row) {
            fputcsv($handle, $row, $delimiter, escape: '\\');
        }

        fclose($handle);

        return $path;
    }

    private const REQUIRED = ['sku', 'name', 'category', 'unit', 'price', 'drug_class', 'weight_grams'];

    public function test_a_row_creates_a_product_with_every_mapped_field(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'PBF Sehat']);

        $path = $this->writeCsv(
            [...self::REQUIRED, 'slug', 'supplier', 'old_price', 'cost_price', 'requires_prescription', 'max_qty_per_order', 'storage', 'status', 'nie_bpom'],
            [['PRD-900', 'Paracetamol 500', 'Obat Bebas', 'Strip', '5000', 'bebas', '20', 'paracetamol-500', 'PBF Sehat', '6000', '3500', 'ya', '5', 'sejuk', 'aktif', 'DBL123']],
        );

        $summary = (new ProductTemplateImporter)->import($path);

        $this->assertSame(1, $summary['created']);
        $this->assertSame([], $summary['failed']);

        $product = Product::where('sku', 'PRD-900')->firstOrFail();
        $this->assertSame('Paracetamol 500', $product->name);
        $this->assertSame('paracetamol-500', $product->slug);
        $this->assertSame('Obat Bebas', $product->category->name);
        $this->assertSame($supplier->id, $product->supplier_id);
        $this->assertSame(5000, $product->price);
        $this->assertSame(6000, $product->old_price);
        $this->assertSame(3500, $product->cost_price);
        $this->assertTrue($product->requires_prescription);
        $this->assertSame(5, $product->max_qty_per_order);
        $this->assertSame('sejuk', $product->storage);
        $this->assertSame('DBL123', $product->nie_bpom);
        $this->assertSame(20, $product->weight_grams);
    }

    public function test_a_missing_slug_is_made_from_the_name_and_zero_max_qty_means_no_limit(): void
    {
        $path = $this->writeCsv(
            [...self::REQUIRED, 'max_qty_per_order'],
            [['PRD-901', 'Vitamin C Kunyah', 'Suplemen', 'Botol', '85000', 'non-obat', '150', '0']],
        );

        (new ProductTemplateImporter)->import($path);

        $product = Product::where('sku', 'PRD-901')->firstOrFail();
        $this->assertSame('vitamin-c-kunyah', $product->slug);
        $this->assertNull($product->max_qty_per_order);
    }

    public function test_semicolon_files_with_a_byte_order_mark_and_uppercase_headers_are_accepted(): void
    {
        $path = $this->writeCsv(
            array_map('strtoupper', self::REQUIRED),
            [['PRD-902', 'Produk Excel', 'Kesehatan', 'Pcs', '1000', 'bebas', '10']],
            ';',
        );
        file_put_contents($path, "\xEF\xBB\xBF".file_get_contents($path));

        $summary = (new ProductTemplateImporter)->import($path);

        $this->assertSame(1, $summary['created']);
        $this->assertDatabaseHas('products', ['sku' => 'PRD-902']);
    }

    public function test_reimport_updates_in_place_and_keeps_the_slug_unless_one_is_given(): void
    {
        $importer = new ProductTemplateImporter;
        $header = [...self::REQUIRED, 'blurb'];

        $importer->import($this->writeCsv($header, [['PRD-903', 'Nama Awal', 'Kesehatan', 'Pcs', '1000', 'bebas', '10', 'Ringkasan']]));
        $slug = Product::where('sku', 'PRD-903')->value('slug');

        $summary = $importer->import($this->writeCsv($header, [['PRD-903', 'Nama Baru', 'Kesehatan', 'Pcs', '2000', 'bebas', '10', '']]));

        $this->assertSame(1, $summary['updated']);
        $this->assertSame(1, Product::where('sku', 'PRD-903')->count());

        $product = Product::where('sku', 'PRD-903')->first();
        $this->assertSame('Nama Baru', $product->name);
        $this->assertSame(2000, $product->price);
        $this->assertSame($slug, $product->slug);
        $this->assertNull($product->blurb);
    }

    public function test_columns_missing_from_the_file_leave_existing_values_alone(): void
    {
        Product::factory()->create(['sku' => 'PRD-904', 'blurb' => 'Jangan hapus', 'cost_price' => 777]);

        (new ProductTemplateImporter)->import($this->writeCsv(
            self::REQUIRED,
            [['PRD-904', 'Nama Lain', 'Kesehatan', 'Pcs', '1000', 'bebas', '10']],
        ));

        $product = Product::where('sku', 'PRD-904')->first();
        $this->assertSame('Jangan hapus', $product->blurb);
        $this->assertSame(777, $product->cost_price);
    }

    public function test_bebas_terbatas_without_a_warning_imports_as_nonaktif(): void
    {
        $header = [...self::REQUIRED, 'warning', 'status'];

        $summary = (new ProductTemplateImporter)->import($this->writeCsv($header, [
            ['PRD-905', 'Obat Flu', 'Obat Bebas', 'Strip', '9000', 'bebas terbatas', '10', '', 'aktif'],
            ['PRD-906', 'Obat Batuk', 'Obat Bebas', 'Strip', '9000', 'bebas terbatas', '10', 'P no. 1: Awas obat keras', 'aktif'],
        ]));

        $this->assertSame(1, $summary['warnedInactive']);
        $this->assertSame('nonaktif', Product::where('sku', 'PRD-905')->value('status'));
        $this->assertSame('aktif', Product::where('sku', 'PRD-906')->value('status'));
    }

    public function test_bad_rows_are_reported_by_line_and_do_not_stop_the_good_ones(): void
    {
        $path = $this->writeCsv(self::REQUIRED, [
            ['PRD-907', 'Harga Pakai Titik', 'Kesehatan', 'Pcs', '15.000', 'bebas', '10'],
            ['', 'Tanpa SKU', 'Kesehatan', 'Pcs', '1000', 'bebas', '10'],
            ['PRD-908', 'Golongan Salah', 'Kesehatan', 'Pcs', '1000', 'sembarang', '10'],
            ['PRD-909', 'Baris Baik', 'Kesehatan', 'Pcs', '1000', 'bebas', '10'],
        ]);

        $summary = (new ProductTemplateImporter)->import($path);

        $this->assertSame(1, $summary['created']);
        $this->assertSame([2, 3, 4], array_column($summary['failed'], 'row'));
        $this->assertDatabaseMissing('products', ['sku' => 'PRD-907']);
        $this->assertDatabaseHas('products', ['sku' => 'PRD-909']);
    }

    public function test_an_unknown_supplier_and_a_taken_slug_fail_the_row(): void
    {
        Product::factory()->create(['sku' => 'PRD-910', 'slug' => 'sudah-dipakai']);

        $path = $this->writeCsv([...self::REQUIRED, 'supplier', 'slug'], [
            ['PRD-911', 'Pemasok Hantu', 'Kesehatan', 'Pcs', '1000', 'bebas', '10', 'PBF Tidak Ada', ''],
            ['PRD-912', 'Slug Bentrok', 'Kesehatan', 'Pcs', '1000', 'bebas', '10', '', 'sudah-dipakai'],
        ]);

        $summary = (new ProductTemplateImporter)->import($path);

        $this->assertSame(0, $summary['created']);
        $this->assertStringContainsString('PBF Tidak Ada', $summary['failed'][0]['message']);
        $this->assertStringContainsString('sudah-dipakai', $summary['failed'][1]['message']);
    }

    public function test_a_file_missing_a_required_column_is_rejected_outright(): void
    {
        $path = $this->writeCsv(['sku', 'name'], [['PRD-913', 'Tanpa Kolom']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('category');

        (new ProductTemplateImporter)->import($path);
    }

    public function test_a_new_category_name_creates_the_category(): void
    {
        $this->assertDatabaseMissing('categories', ['name' => 'Perawatan Mata']);

        (new ProductTemplateImporter)->import($this->writeCsv(
            self::REQUIRED,
            [['PRD-914', 'Tetes Mata', 'Perawatan Mata', 'Botol', '30000', 'bebas', '20']],
        ));

        $this->assertSame(1, Category::where('name', 'Perawatan Mata')->count());
    }

    public function test_several_image_urls_become_ordered_images_with_the_first_primary(): void
    {
        Storage::fake('public');
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();
        Http::fake(['*' => Http::response($bytes, 200, ['Content-Type' => 'image/jpeg'])]);

        $summary = (new ProductTemplateImporter)->import($this->writeCsv(
            [...self::REQUIRED, 'image_url'],
            [['PRD-915', 'Dengan Foto', 'Kesehatan', 'Pcs', '1000', 'bebas', '10', 'https://img.test/a.jpg|https://img.test/b.jpg']],
        ));

        $this->assertSame([], $summary['imagesFailed']);

        $images = Product::where('sku', 'PRD-915')->first()->images()->orderBy('position')->get();
        $this->assertCount(2, $images);
        $this->assertTrue($images[0]->is_primary);
        $this->assertFalse($images[1]->is_primary);
    }
}

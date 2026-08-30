<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class ProductCsvImportTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Http::fake(['*' => Http::response($this->fakeJpeg(), 200, ['Content-Type' => 'image/jpeg'])]);

        $this->seed();
        $this->signInAsAdmin();
    }

    private function fakeJpeg(): string
    {
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private const HEADER = [
        'Handle', 'Title', 'Body (HTML)', 'Vendor', 'Product Category', 'Type', 'Tags', 'Published',
        'Option1 Name', 'Option1 Value', 'Option1 Linked To', 'Option2 Name', 'Option2 Value', 'Option2 Linked To',
        'Option3 Name', 'Option3 Value', 'Option3 Linked To', 'Variant SKU', 'Variant Grams',
        'Variant Inventory Tracker', 'Variant Inventory Policy', 'Variant Fulfillment Service', 'Variant Price',
        'Variant Compare At Price', 'Variant Requires Shipping', 'Variant Taxable', 'Unit Price Total Measure',
        'Unit Price Total Measure Unit', 'Unit Price Base Measure', 'Unit Price Base Measure Unit',
        'Variant Barcode', 'Image Src', 'Image Position', 'Image Alt Text', 'Gift Card', 'SEO Title',
        'SEO Description', 'Max Qty (product.metafields.custom.max_qty)',
        'Alamat Apotek (product.metafields.vendor_info.address)',
        'Nama Apoteker (product.metafields.vendor_info.apoteker)',
        'Waktu Praktik (product.metafields.vendor_info.practice_hours)',
        'Jadwal Toko (product.metafields.vendor_info.schedule)', 'Nomor SIA (product.metafields.vendor_info.sia)',
        'Nomor SIPA (product.metafields.vendor_info.sipa)', 'Variant Image', 'Variant Weight Unit',
        'Variant Tax Code', 'Cost per item', 'Status',
    ];

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function csvUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csvtest').'.csv';
        $handle = fopen($path, 'w');
        fputcsv($handle, self::HEADER);

        foreach ($rows as $row) {
            $line = array_fill(0, count(self::HEADER), '');

            foreach ($row as $key => $value) {
                $line[array_search($key, self::HEADER, true)] = $value;
            }

            fputcsv($handle, $line);
        }

        fclose($handle);

        return new UploadedFile($path, 'produk.csv', 'text/csv', null, true);
    }

    private function bodyHtml(string $kemasan, string $deskripsi, string $komposisi, string $golongan, string $nie = 'DTL123456'): string
    {
        return "<p>{$kemasan}</p><p>Deskripsi: {$deskripsi}</p><p>Komposisi: {$komposisi}</p>"
            ."<p>Golongan Obat: {$golongan}</p><p>Bentuk Sediaan: SIRUP</p><p>NIE:{$nie}</p><p>Exp Date:08/2027</p>";
    }

    public function test_a_csv_row_creates_a_product_with_the_mapped_fields(): void
    {
        $this->post('/admin/produk/impor', [
            'file' => $this->csvUpload([[
                'Handle' => 'obat-batuk-anak',
                'Title' => 'Obat Batuk Anak',
                'Body (HTML)' => $this->bodyHtml(
                    'Dus, 1 Botol Plastik @ 60 Ml',
                    'Obat yang berfungsi untuk meredakan batuk pada anak.',
                    'Paracetamol 120 mg',
                    'GREEN',
                ),
                'Tags' => 'Kesehatan, mqty:5',
                'Variant SKU' => 'TEST-001',
                'Variant Grams' => '60',
                'Variant Price' => '15000.00',
                'Image Src' => 'https://cdn.example.com/test-001.jpg',
            ]]),
        ])->assertSessionHas('success');

        $product = Product::where('sku', 'TEST-001')->firstOrFail();

        $this->assertSame('Obat Batuk Anak', $product->name);
        $this->assertSame('obat-batuk-anak', $product->slug);
        $this->assertSame('Kesehatan', $product->category->name);
        $this->assertSame(15000, $product->price);
        $this->assertSame(60, $product->weight_grams);
        $this->assertSame('bebas', $product->drug_class);
        $this->assertSame('aktif', $product->status);
        $this->assertSame(5, $product->max_qty_per_order);
        $this->assertSame('DTL123456', $product->nie_bpom);
        $this->assertSame('Paracetamol 120 mg', $product->composition);
        $this->assertSame('Botol', $product->unit);
        $this->assertTrue($product->images()->exists());
    }

    public function test_reimporting_the_same_sku_updates_instead_of_duplicating(): void
    {
        $row = [
            'Handle' => 'obat-batuk-anak',
            'Title' => 'Obat Batuk Anak',
            'Body (HTML)' => $this->bodyHtml('Dus, 1 Botol @ 60 Ml', 'Deskripsi awal.', 'Paracetamol', 'GREEN'),
            'Tags' => 'Kesehatan, mqty:0',
            'Variant SKU' => 'TEST-001',
            'Variant Grams' => '60',
            'Variant Price' => '15000.00',
        ];

        $this->post('/admin/produk/impor', ['file' => $this->csvUpload([$row])]);

        $row['Variant Price'] = '18000.00';
        $this->post('/admin/produk/impor', ['file' => $this->csvUpload([$row])]);

        $this->assertSame(1, Product::where('sku', 'TEST-001')->count());
        $this->assertSame(18000, Product::where('sku', 'TEST-001')->first()->price);
    }

    public function test_bebas_terbatas_products_import_as_nonaktif_pending_a_p1_p6_warning(): void
    {
        $this->post('/admin/produk/impor', [
            'file' => $this->csvUpload([[
                'Handle' => 'obat-flu-dewasa',
                'Title' => 'Obat Flu Dewasa',
                'Body (HTML)' => $this->bodyHtml('Dus, 1 Botol @ 60 Ml', 'Deskripsi.', 'Pseudoephedrine', 'BLUE'),
                'Tags' => 'Obat Bebas, mqty:0',
                'Variant SKU' => 'TEST-002',
                'Variant Price' => '20000.00',
            ]]),
        ])->assertInertia(fn (AssertableInertia $page) => $page->where('result.warnedInactive', 1));

        $product = Product::where('sku', 'TEST-002')->firstOrFail();

        $this->assertSame('bebas terbatas', $product->drug_class);
        $this->assertSame('nonaktif', $product->status);
        $this->assertNull($product->max_qty_per_order);
        $this->assertTrue($product->needs_warning_label);
    }

    public function test_a_row_with_no_sku_is_skipped_without_aborting_the_rest(): void
    {
        $badRow = [
            'Handle' => 'row-tanpa-sku',
            'Title' => 'Tanpa SKU',
            'Body (HTML)' => $this->bodyHtml('Dus', 'Deskripsi.', 'Komposisi.', 'GREEN'),
            'Tags' => 'Kesehatan, mqty:0',
            'Variant SKU' => '',
            'Variant Price' => '1000.00',
        ];

        $goodRow = $badRow;
        $goodRow['Handle'] = 'obat-baik';
        $goodRow['Variant SKU'] = 'TEST-003';

        $this->post('/admin/produk/impor', ['file' => $this->csvUpload([$badRow, $goodRow])])
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('result.created', 1)
                ->where('result.failed.0.row', 2)
            );

        $this->assertSame(1, Product::where('sku', 'TEST-003')->count());
    }

    public function test_an_unseeded_category_tag_creates_the_category(): void
    {
        $this->assertDatabaseMissing('categories', ['name' => 'Perawatan Gigi']);

        $this->post('/admin/produk/impor', [
            'file' => $this->csvUpload([[
                'Handle' => 'sikat-gigi-keluarga',
                'Title' => 'Sikat Gigi Keluarga',
                'Body (HTML)' => $this->bodyHtml('Dus, 1 Pcs', 'Deskripsi.', 'Komposisi.', 'PKRT'),
                'Tags' => 'Perawatan Gigi, mqty:0',
                'Variant SKU' => 'TEST-004',
                'Variant Price' => '5000.00',
            ]]),
        ]);

        $this->assertDatabaseHas('categories', ['name' => 'Perawatan Gigi', 'status' => 'aktif']);
        $this->assertSame('non-obat', Product::where('sku', 'TEST-004')->first()->drug_class);
    }
}

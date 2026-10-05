<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\ProductCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($path, 'w');
        fputcsv($handle, ['Handle', 'Title', 'Body (HTML)', 'Tags', 'Variant SKU', 'Variant Grams', 'Variant Price', 'Variant Compare At Price', 'Image Src'], escape: '\\');

        foreach ($rows as $row) {
            fputcsv($handle, $row, escape: '\\');
        }

        fclose($handle);

        return $path;
    }

    public function test_imports_rows_and_reports_failed_image_downloads_without_aborting(): void
    {
        Http::fake([
            'img.test/ok.jpg' => Http::response('not-a-real-image', 200),
            'img.test/missing.jpg' => Http::response('', 404),
        ]);

        $body = '<p>Dus, 1 Botol @ 60 Ml</p><p>Golongan Obat: GREEN</p>';
        $path = $this->writeCsv([
            ['a', 'Produk A', $body, 'mqty:1, Obat Bebas', 'SKU-A', '10', '15000', '', 'https://img.test/ok.jpg'],
            ['b', 'Produk B', $body, 'mqty:1, Obat Bebas', 'SKU-B', '10', '20000', '', 'https://img.test/missing.jpg'],
            ['c', 'Produk C', $body, 'mqty:1, Obat Bebas', 'SKU-C', '10', '20000', '', ''],
        ]);

        $summary = (new ProductCsvImporter)->import($path);

        $this->assertSame(3, $summary['created']);
        $this->assertSame([], $summary['failed']);
        $this->assertEqualsCanonicalizing(['SKU-A', 'SKU-B'], $summary['imagesFailed']);
        $this->assertSame(3, Product::count());
    }

    public function test_reimport_updates_instead_of_duplicating_and_empty_sku_is_a_row_error(): void
    {
        $body = '<p>Strip</p><p>Golongan Obat: GREEN</p>';
        $path = $this->writeCsv([
            ['a', 'Produk A', $body, 'Kesehatan', 'SKU-A', '10', '15000', '', ''],
            ['x', 'Tanpa SKU', $body, 'Kesehatan', '', '10', '15000', '', ''],
        ]);

        $importer = new ProductCsvImporter;
        $first = $importer->import($path);
        $second = $importer->import($path);

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $second['updated']);
        $this->assertSame(3, $second['failed'][0]['row']);
        $this->assertSame(1, Product::count());
    }
}

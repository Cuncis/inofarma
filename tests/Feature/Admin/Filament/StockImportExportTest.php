<?php

namespace Tests\Feature\Admin\Filament;

use App\Filament\Resources\BranchStocks\Pages\ListBranchStocks;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Support\CsvTemplates\ImportFailures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class StockImportExportTest extends TestCase
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
        return UploadedFile::fake()->createWithContent('stok.csv', $content);
    }

    private function signInAs(string $roleName, array $permissions, ?int $branchId = null): User
    {
        $this->post('/admin/keluar');

        Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_active' => true, 'branch_id' => $branchId]);
        $user->assignRole($roleName);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password']);

        return $user;
    }

    public function test_importing_replaces_batches_for_the_branch_and_reports_success(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-850']);
        $product = Product::factory()->create(['sku' => 'STK-IMP', 'supplier_id' => null]);
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 9]);
        $old = InventoryBatch::factory()->for($branch)->for($product)->create(['batch_number' => 'OLD', 'quantity' => 9]);

        $csv = "sku,branch_code,batch_number,expires_at,quantity\nSTK-IMP,CB-850,NEW1,2028-05-31,64\n";

        Livewire::test(ListBranchStocks::class)
            ->callAction('importStock', data: ['file' => $this->upload($csv)])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertSame(0, $old->fresh()->quantity);
        $this->assertSame(64, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_the_combined_files_branch_header_is_accepted_too(): void
    {
        $branch = Branch::factory()->create(['code' => 'CB-851']);
        $product = Product::factory()->create(['sku' => 'STK-ALIAS', 'supplier_id' => null]);

        $csv = "sku,branch,batch_number,expires_at,quantity\nSTK-ALIAS,CB-851,B1,2028-05-31,12\n";

        Livewire::test(ListBranchStocks::class)->callAction('importStock', data: ['file' => $this->upload($csv)]);

        $this->assertSame(12, BranchStock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_many_rows_failing_for_one_reason_are_reported_once(): void
    {
        Product::factory()->create(['sku' => 'STK-GRP', 'supplier_id' => null]);
        $rows = collect(range(1, 12))->map(fn (int $i) => "STK-GRP,Cabang Hantu,B{$i},2028-05-31,5")->implode("\n");

        Livewire::test(ListBranchStocks::class)
            ->callAction('importStock', data: ['file' => $this->upload("sku,branch_code,batch_number,expires_at,quantity\n{$rows}\n")])
            ->assertNotified();

        $this->assertDatabaseMissing('inventory_batches', ['batch_number' => 'B1']);
    }

    public function test_the_failure_grouping_folds_one_message_into_one_entry_with_ten_row_numbers(): void
    {
        $failed = collect(range(2, 13))->map(fn (int $row) => ['row' => $row, 'message' => 'Cabang "X" tidak ditemukan'])->all();
        $failed[] = ['row' => 14, 'message' => 'Kolom quantity wajib diisi.'];

        $groups = ImportFailures::group($failed);

        $this->assertCount(2, $groups);
        $this->assertSame(12, $groups[0]['count']);
        $this->assertSame(range(2, 11), $groups[0]['rows']);
        $this->assertSame(2, $groups[0]['more']);
        $this->assertSame(1, $groups[1]['count']);
        $this->assertSame(0, $groups[1]['more']);
    }

    public function test_a_file_missing_required_columns_gives_an_error_notification(): void
    {
        Livewire::test(ListBranchStocks::class)
            ->callAction('importStock', data: ['file' => $this->upload("sku,quantity\nA,1\n")])
            ->assertNotified(__('Import gagal'));
    }

    public function test_export_and_template_download_as_csv(): void
    {
        Livewire::test(ListBranchStocks::class)
            ->callAction('exportStock')
            ->assertFileDownloaded();

        Livewire::test(ListBranchStocks::class)
            ->callAction('downloadStockTemplate')
            ->assertFileDownloaded('template-stok.csv');
    }

    public function test_importing_needs_the_adjust_stock_permission(): void
    {
        $this->signInAs('Hanya Lihat Stok', ['Inventaris:Lihat']);

        Livewire::test(ListBranchStocks::class)
            ->assertActionHidden('importStock')
            ->assertActionVisible('exportStock');
    }

    public function test_a_branch_confined_staff_member_can_only_import_their_own_branch(): void
    {
        $own = Branch::factory()->create(['code' => 'CB-852']);
        Branch::factory()->create(['code' => 'CB-853']);
        Product::factory()->create(['sku' => 'STK-OWN', 'supplier_id' => null]);

        $this->signInAs('Gudang Cabang', ['Inventaris:Lihat', 'Inventaris:Sesuaikan Stok'], $own->id);

        $csv = "sku,branch_code,batch_number,expires_at,quantity\nSTK-OWN,CB-852,B1,2028-05-31,5\nSTK-OWN,CB-853,B1,2028-05-31,5\n";

        Livewire::test(ListBranchStocks::class)->callAction('importStock', data: ['file' => $this->upload($csv)]);

        $this->assertSame(1, InventoryBatch::where('batch_number', 'B1')->count());
        $this->assertSame($own->id, InventoryBatch::where('batch_number', 'B1')->value('branch_id'));
    }
}

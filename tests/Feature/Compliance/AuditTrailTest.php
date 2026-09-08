<?php

namespace Tests\Feature\Compliance;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\Inventory\StockAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * ROADMAP.md Fase 9.3: "jejak audit untuk setiap perubahan data obat dan
 * setiap penjualan, per cabang" — the gap Fase 3 explicitly deferred
 * ("Belum dipasang di CRUD Produk/.../Pesanan"), closed here.
 */
class AuditTrailTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_adding_a_product_is_logged(): void
    {
        $category = Category::first();
        $supplier = Supplier::first();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Parasetamol 500mg', 'category_id' => $category->id, 'supplier_id' => $supplier->id,
                'unit' => 'Strip', 'status' => 'aktif', 'price' => 12000, 'requires_prescription' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Parasetamol 500mg')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'produk_ditambahkan',
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
        ]);
    }

    public function test_changing_a_products_drug_data_is_logged_with_before_and_after(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'drug_class' => 'bebas']);

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm([
                'name' => $product->name, 'category_id' => $product->category_id, 'supplier_id' => $product->supplier_id,
                'unit' => $product->unit, 'status' => 'aktif', 'price' => 15000, 'requires_prescription' => false,
                'drug_class' => 'bebas terbatas', 'warning' => 'P1 Awas! Obat Keras.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $log = AuditLog::where('action', 'produk_diubah')->where('auditable_id', $product->id)->first();

        $this->assertNotNull($log);
        $this->assertSame(10000, $log->old_values['price']);
        $this->assertSame(15000, $log->new_values['price']);
        $this->assertSame('bebas', $log->old_values['drug_class']);
        $this->assertSame('bebas terbatas', $log->new_values['drug_class']);
    }

    public function test_deleting_a_product_is_logged(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('delete');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'produk_dihapus',
        ]);
    }

    public function test_every_sale_is_logged_per_branch(): void
    {
        $branch = Branch::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        BranchStock::factory()->for($branch)->for($product)->create(['quantity' => 10]);
        InventoryBatch::factory()->for($branch)->for($product)->create(['quantity' => 10, 'expires_at' => now()->addYear()]);

        $order = Order::factory()->create(['branch_id' => $branch->id, 'customer_id' => $customer->id]);
        (new StockAllocator)->consume($branch, $product, 1, 'penjualan', $order);

        $log = AuditLog::where('action', 'pesanan_dibuat')->where('auditable_id', $order->id)->first();

        $this->assertNotNull($log);
        $this->assertSame($branch->id, $log->branch_id);
        $this->assertSame($order->number, $log->new_values['number']);
    }
}

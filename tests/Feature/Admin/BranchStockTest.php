<?php

namespace Tests\Feature\Admin;

use App\Models\BranchStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class BranchStockTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_branch_picker_lists_every_branch(): void
    {
        $this->get('/admin/inventaris/stok')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/BranchStockList')
                ->has('branches', self::BRANCH_COUNT)
            );
    }

    public function test_one_branchs_stock_lists_every_seeded_product(): void
    {
        $this->get('/admin/inventaris/stok/CB-001')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/BranchStockDetail')
                ->where('branch.id', 'CB-001')
                ->has('stocks', self::PRODUCT_COUNT)
            );
    }

    public function test_an_unknown_branch_is_a_404(): void
    {
        $this->get('/admin/inventaris/stok/CB-999')->assertNotFound();
    }

    public function test_stock_can_be_adjusted_up(): void
    {
        $before = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');

        $this->post('/admin/inventaris/stok/CB-001/PRD-001/sesuaikan', [
            'delta' => 20,
            'reason' => 'Penyesuaian (stok opname)',
            'note' => 'Hasil hitung ulang.',
        ])
            ->assertRedirect(route('admin.inventaris.stok.show', 'CB-001'))
            ->assertSessionHas('success');

        $after = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');

        $this->assertSame($before + 20, $after);
    }

    public function test_stock_cannot_be_adjusted_below_zero(): void
    {
        $this->post('/admin/inventaris/stok/CB-001/PRD-001/sesuaikan', [
            'delta' => -100000,
            'reason' => 'Rusak',
        ])
            ->assertRedirect(route('admin.inventaris.stok.show', 'CB-001'))
            ->assertSessionHas('error');
    }

    public function test_adjustment_requires_a_nonzero_delta_and_a_known_reason(): void
    {
        $this->post('/admin/inventaris/stok/CB-001/PRD-001/sesuaikan', [
            'delta' => 0,
            'reason' => 'Karena saya mau',
        ])->assertSessionHasErrors(['delta', 'reason']);
    }

    public function test_receiving_stock_creates_a_new_batch_with_an_expiry(): void
    {
        $this->post('/admin/inventaris/stok/CB-001/PRD-001/terima', [
            'batchNumber' => 'B-NEW-001',
            'expiresAt' => now()->addYear()->toDateString(),
            'quantity' => 100,
            'costPrice' => 8000,
        ])
            ->assertRedirect(route('admin.inventaris.stok.show', 'CB-001'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_batches', [
            'batch_number' => 'B-NEW-001', 'quantity' => 100, 'cost_price' => 8000,
        ]);
    }

    public function test_receiving_requires_a_future_expiry_date(): void
    {
        $this->post('/admin/inventaris/stok/CB-001/PRD-001/terima', [
            'batchNumber' => 'B-EXPIRED',
            'expiresAt' => now()->subDay()->toDateString(),
            'quantity' => 10,
        ])->assertSessionHasErrors('expiresAt');
    }
}

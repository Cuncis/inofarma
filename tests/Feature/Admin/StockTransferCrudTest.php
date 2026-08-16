<?php

namespace Tests\Feature\Admin;

use App\Models\BranchStock;
use App\Models\StockTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class StockTransferCrudTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    /**
     * CB-001 always carries stock on every seeded product — see CatalogSeeder.
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'fromBranch' => 'CB-001',
            'toBranch' => 'CB-002',
            'product' => 'PRD-001',
            'quantity' => 5,
            'note' => 'Permintaan cabang.',
        ], $overrides);
    }

    public function test_a_transfer_can_be_requested(): void
    {
        $this->post('/admin/inventaris/transfer', $this->validPayload())
            ->assertRedirect(route('admin.inventaris.transfer.index'))
            ->assertSessionHas('success');

        $this->get('/admin/inventaris/transfer')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/StockTransferList')
                ->has('transfers', 1)
                ->where('transfers.0.status', 'Diminta')
                ->where('transfers.0.fromBranch', 'CB-001')
                ->where('transfers.0.toBranch', 'CB-002')
            );
    }

    public function test_the_origin_and_destination_must_differ(): void
    {
        $this->post('/admin/inventaris/transfer', $this->validPayload(['toBranch' => 'CB-001']))
            ->assertSessionHasErrors('toBranch');
    }

    public function test_requesting_more_than_the_origin_holds_is_refused(): void
    {
        $this->post('/admin/inventaris/transfer', $this->validPayload(['quantity' => 100000]))
            ->assertSessionHasErrors('quantity');
    }

    public function test_a_full_lifecycle_moves_stock_between_branches(): void
    {
        // Both branches already carry PRD-001 from the seed, so the assertion
        // below is about the delta each side moves, not an absolute count.
        $originBefore = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');
        $destinationBefore = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-002'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');

        $this->post('/admin/inventaris/transfer', $this->validPayload());
        $code = StockTransfer::first()->code;

        $this->post("/admin/inventaris/transfer/{$code}/kirim")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get("/admin/inventaris/transfer/{$code}")
            ->assertInertia(fn (AssertableInertia $page) => $page->where('transfer.status', 'Dikirim'));

        $this->post("/admin/inventaris/transfer/{$code}/terima")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get("/admin/inventaris/transfer/{$code}")
            ->assertInertia(fn (AssertableInertia $page) => $page->where('transfer.status', 'Diterima'));

        $originAfter = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-001'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');
        $destinationAfter = BranchStock::whereHas('branch', fn ($q) => $q->where('code', 'CB-002'))
            ->whereHas('product', fn ($q) => $q->where('sku', 'PRD-001'))
            ->value('quantity');

        $this->assertSame($originBefore - 5, $originAfter);
        $this->assertSame($destinationBefore + 5, $destinationAfter);
    }

    public function test_a_pending_request_can_be_cancelled(): void
    {
        $this->post('/admin/inventaris/transfer', $this->validPayload());
        $code = StockTransfer::first()->code;

        $this->post("/admin/inventaris/transfer/{$code}/batalkan")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get("/admin/inventaris/transfer/{$code}")
            ->assertInertia(fn (AssertableInertia $page) => $page->where('transfer.status', 'Dibatalkan'));
    }

    public function test_a_shipped_transfer_cannot_be_cancelled_through_the_endpoint(): void
    {
        $this->post('/admin/inventaris/transfer', $this->validPayload());
        $code = StockTransfer::first()->code;
        $this->post("/admin/inventaris/transfer/{$code}/kirim");

        $this->post("/admin/inventaris/transfer/{$code}/batalkan")
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_an_unknown_transfer_is_a_404(): void
    {
        $this->get('/admin/inventaris/transfer/TRF-999')->assertNotFound();
    }
}

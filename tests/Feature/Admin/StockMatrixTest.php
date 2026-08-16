<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SeedsDemoCatalogue;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

class StockMatrixTest extends TestCase
{
    use RefreshDatabase, SeedsDemoCatalogue, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();
    }

    public function test_the_matrix_has_one_row_per_product_and_one_cell_per_branch(): void
    {
        $this->get('/admin/inventaris/matriks')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/StockMatrix')
                ->has('branches', self::BRANCH_COUNT)
                ->has('rows', self::PRODUCT_COUNT)
                ->has('rows.0.cells', self::BRANCH_COUNT)
            );
    }

    public function test_the_same_product_shows_different_quantities_across_branches(): void
    {
        $response = $this->get('/admin/inventaris/matriks');
        $cells = $response->viewData('page')['props']['rows'][0]['cells'];

        $quantities = collect($cells)->pluck('quantity');

        $this->assertGreaterThan(1, $quantities->unique()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use Database\Seeders\BranchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_four_branches_with_sequential_codes(): void
    {
        $this->seed(BranchSeeder::class);

        $this->assertSame(
            ['Apotek Inofarma Jengki', 'Apotek Inofarma Kalisari', 'Apotek Inofarma Kayu Manis', 'Apotek Inofarma Pisangan Lama'],
            Branch::orderBy('id')->pluck('name')->all(),
        );
        $this->assertSame(['CB-001', 'CB-002', 'CB-003', 'CB-004'], Branch::orderBy('id')->pluck('code')->all());
    }

    public function test_running_it_again_neither_duplicates_nor_overwrites_admin_edits(): void
    {
        $this->seed(BranchSeeder::class);
        Branch::where('name', 'Apotek Inofarma Kalisari')->update(['address_line' => 'Jl. Kalisari Raya No. 1', 'latitude' => -6.3]);

        $this->seed(BranchSeeder::class);

        $this->assertSame(4, Branch::count());
        $kalisari = Branch::where('name', 'Apotek Inofarma Kalisari')->first();
        $this->assertSame('Jl. Kalisari Raya No. 1', $kalisari->address_line);
        $this->assertNotNull($kalisari->latitude);
    }

    public function test_existing_branches_are_left_alone_and_new_codes_continue_the_series(): void
    {
        $old = Branch::factory()->create(['code' => 'CB-010', 'name' => 'Apotek Inofarma Kebagusan']);

        $this->seed(BranchSeeder::class);

        $this->assertSame('Apotek Inofarma Kebagusan', $old->fresh()->name);
        $this->assertSame(5, Branch::count());
        $this->assertSame('CB-011', Branch::where('name', 'Apotek Inofarma Jengki')->value('code'));
        $this->assertSame('CB-014', Branch::where('name', 'Apotek Inofarma Pisangan Lama')->value('code'));
    }
}

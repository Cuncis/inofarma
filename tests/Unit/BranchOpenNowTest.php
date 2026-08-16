<?php

namespace Tests\Unit;

use App\Models\Branch;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BranchOpenNowTest extends TestCase
{
    private function branch(array $overrides = []): Branch
    {
        return new Branch([
            'status' => 'aktif',
            'operating_hours' => [
                'senin' => ['open' => '08:00', 'close' => '21:00'],
                'selasa' => ['open' => '08:00', 'close' => '21:00'],
                'rabu' => ['open' => '08:00', 'close' => '21:00'],
                'kamis' => ['open' => '08:00', 'close' => '21:00'],
                'jumat' => ['open' => '08:00', 'close' => '21:00'],
                'sabtu' => ['open' => '08:00', 'close' => '21:00'],
                'minggu' => ['open' => '09:00', 'close' => '20:00'],
            ],
            ...$overrides,
        ]);
    }

    public function test_open_during_posted_hours(): void
    {
        // 2026-08-17 is a Monday.
        $branch = $this->branch();

        $this->assertTrue($branch->isOpenAt(Carbon::parse('2026-08-17 10:00')));
    }

    public function test_closed_before_opening(): void
    {
        $branch = $this->branch();

        $this->assertFalse($branch->isOpenAt(Carbon::parse('2026-08-17 07:59')));
    }

    public function test_closed_after_closing(): void
    {
        $branch = $this->branch();

        $this->assertFalse($branch->isOpenAt(Carbon::parse('2026-08-17 21:01')));
    }

    public function test_sunday_uses_its_own_shorter_hours(): void
    {
        // 2026-08-16 is a Sunday.
        $branch = $this->branch();

        $this->assertTrue($branch->isOpenAt(Carbon::parse('2026-08-16 19:00')));
        $this->assertFalse($branch->isOpenAt(Carbon::parse('2026-08-16 20:30')));
    }

    public function test_a_day_missing_from_the_schedule_reads_as_closed(): void
    {
        $branch = $this->branch(['operating_hours' => ['senin' => ['open' => '08:00', 'close' => '21:00']]]);

        // Tuesday has no entry in this schedule.
        $this->assertFalse($branch->isOpenAt(Carbon::parse('2026-08-18 10:00')));
    }

    public function test_a_temporarily_closed_branch_is_never_open_now_regardless_of_hours(): void
    {
        $branch = $this->branch(['status' => 'tutup sementara']);

        $this->assertTrue($branch->isOpenAt(Carbon::parse('2026-08-17 10:00')));
        Carbon::setTestNow(Carbon::parse('2026-08-17 10:00'));

        $this->assertFalse($branch->is_open_now);

        Carbon::setTestNow();
    }
}

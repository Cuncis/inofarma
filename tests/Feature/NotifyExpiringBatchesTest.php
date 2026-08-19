<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Admin\ExpiringProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * `notifikasi:produk-kedaluwarsa` — the 30-day expiry warning sweep
 * (ROADMAP.md Fase 8), and its dedup guard.
 */
class NotifyExpiringBatchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_batch_inside_the_warning_window_notifies_branch_staff_and_is_marked(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $batch = InventoryBatch::factory()->for($branch)->for(Product::factory())->create([
            'quantity' => 5, 'expires_at' => now()->addDays(10),
        ]);

        $this->artisan('notifikasi:produk-kedaluwarsa')->assertSuccessful();

        Notification::assertSentTo($staff, ExpiringProduct::class);
        $this->assertNotNull($batch->fresh()->expiry_reminder_sent_at);
    }

    public function test_the_same_batch_is_not_renotified_on_a_second_run(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        InventoryBatch::factory()->for($branch)->for(Product::factory())->create([
            'quantity' => 5, 'expires_at' => now()->addDays(10),
        ]);

        $this->artisan('notifikasi:produk-kedaluwarsa');
        $this->artisan('notifikasi:produk-kedaluwarsa');

        Notification::assertSentToTimes($staff, ExpiringProduct::class, 1);
    }

    public function test_a_batch_far_from_expiry_or_already_empty_is_left_alone(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        InventoryBatch::factory()->for($branch)->for(Product::factory())->create([
            'quantity' => 5, 'expires_at' => now()->addYear(),
        ]);
        InventoryBatch::factory()->for($branch)->for(Product::factory())->create([
            'quantity' => 0, 'expires_at' => now()->addDays(5),
        ]);

        $this->artisan('notifikasi:produk-kedaluwarsa');

        Notification::assertNothingSentTo($staff);
    }
}

<?php

namespace Tests\Feature\Notifications;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Admin\LowStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * `App\Observers\BranchStockObserver`'s crossing-the-threshold behaviour —
 * moved out of the old AdminNotificationTest (Fase 8) since this is purely
 * about the observer/notification firing, not the admin UI that reads it.
 */
class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crossing_the_reorder_point_notifies_branch_staff_once(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $product = Product::factory()->create();
        $stock = BranchStock::factory()->for($branch)->for($product)->create([
            'quantity' => 50, 'reserved_quantity' => 0, 'reorder_point' => 20,
        ]);

        $stock->update(['quantity' => 15]);
        Notification::assertSentTo($staff, LowStock::class);

        // Already low — selling one more must not renotify.
        $stock->update(['quantity' => 14]);
        Notification::assertSentToTimes($staff, LowStock::class, 1);
    }

    public function test_a_change_that_does_not_cross_the_threshold_notifies_nobody(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $product = Product::factory()->create();
        $stock = BranchStock::factory()->for($branch)->for($product)->create([
            'quantity' => 50, 'reserved_quantity' => 0, 'reorder_point' => 20,
        ]);

        $stock->update(['quantity' => 40]);

        Notification::assertNothingSentTo($staff);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Admin\PickupDeadlineApproaching;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * `notifikasi:pengambilan-mendekati-batas` — the 6-hour pickup-deadline
 * reminder sweep (ROADMAP.md Fase 8), and its dedup guard.
 */
class NotifyApproachingPickupDeadlinesTest extends TestCase
{
    use RefreshDatabase;

    private function makeReadyOrder(Branch $branch, array $overrides = []): Order
    {
        return Order::factory()->pickup()->create(array_merge([
            'branch_id' => $branch->id, 'customer_id' => Customer::factory(),
            'status' => 'siap diambil', 'pickup_code' => '123456',
            'pickup_code_expires_at' => now()->addHours(2),
        ], $overrides));
    }

    public function test_an_order_inside_the_warning_window_notifies_branch_staff_and_is_marked(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $order = $this->makeReadyOrder($branch);

        $this->artisan('notifikasi:pengambilan-mendekati-batas')->assertSuccessful();

        Notification::assertSentTo($staff, PickupDeadlineApproaching::class);
        $this->assertNotNull($order->fresh()->pickup_reminder_sent_at);
    }

    public function test_the_same_order_is_not_renotified_on_a_second_run(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $this->makeReadyOrder($branch);

        $this->artisan('notifikasi:pengambilan-mendekati-batas');
        $this->artisan('notifikasi:pengambilan-mendekati-batas');

        Notification::assertSentToTimes($staff, PickupDeadlineApproaching::class, 1);
    }

    public function test_an_order_well_within_its_window_is_left_alone(): void
    {
        Notification::fake();

        $branch = Branch::factory()->create();
        $staff = User::factory()->create(['branch_id' => $branch->id, 'is_active' => true]);
        $this->makeReadyOrder($branch, ['pickup_code_expires_at' => now()->addHours(20)]);

        $this->artisan('notifikasi:pengambilan-mendekati-batas');

        Notification::assertNotSentTo($staff, PickupDeadlineApproaching::class);
    }
}

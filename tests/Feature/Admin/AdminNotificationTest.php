<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Admin\LowStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * The admin topbar bell (Fase 8): `App\Observers\BranchStockObserver`'s
 * crossing-the-threshold behaviour, and the two actions the bell itself
 * exposes (mark one read, mark all read).
 */
class AdminNotificationTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

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

    public function test_the_bell_shows_the_signed_in_admins_own_unread_count(): void
    {
        $this->signInAsAdmin();
        $admin = User::where('email', 'admin@inofarma.co.id')->first();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));

        $this->get('/admin')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('adminNotifications.unreadCount', 1)
            ->has('adminNotifications.items', 1)
        );
    }

    public function test_marking_a_notification_read_clears_it_from_the_unread_count(): void
    {
        $this->signInAsAdmin();
        $admin = User::where('email', 'admin@inofarma.co.id')->first();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));
        $id = $admin->notifications()->first()->id;

        $this->post("/admin/notifikasi/{$id}/baca")->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_marking_all_read_clears_every_unread_notification(): void
    {
        $this->signInAsAdmin();
        $admin = User::where('email', 'admin@inofarma.co.id')->first();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));

        $this->post('/admin/notifikasi/baca-semua')->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }
}

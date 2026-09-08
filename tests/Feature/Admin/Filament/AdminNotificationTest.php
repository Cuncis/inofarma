<?php

namespace Tests\Feature\Admin\Filament;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Admin\LowStock;
use Filament\Facades\Filament;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SignsInAsAdmin;
use Tests\TestCase;

/**
 * The admin topbar bell, now Filament's own built-in database notifications
 * feature (`->databaseNotifications()` on AdminPanelProvider) instead of the
 * old NotificationController — see `App\Notifications\Admin\LowStock`, which
 * already writes to the standard `database` channel unchanged.
 */
class AdminNotificationTest extends TestCase
{
    use RefreshDatabase, SignsInAsAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->signInAsAdmin();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function admin(): User
    {
        return User::where('email', 'admin@inofarma.co.id')->firstOrFail();
    }

    public function test_the_bell_shows_the_signed_in_admins_own_unread_count(): void
    {
        $admin = $this->admin();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));

        $component = Livewire::test(DatabaseNotifications::class);
        $this->assertSame(1, $component->instance()->getUnreadNotificationsCount());
        $component->assertSee('Stok menipis');
    }

    public function test_marking_a_notification_read_clears_it_from_the_unread_count(): void
    {
        $admin = $this->admin();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));
        $id = $admin->notifications()->first()->id;

        Livewire::test(DatabaseNotifications::class)
            ->call('markNotificationAsRead', $id);

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_marking_all_read_clears_every_unread_notification(): void
    {
        $admin = $this->admin();
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));
        $admin->notify(new LowStock(BranchStock::factory()->for(Branch::factory())->for(Product::factory())->create(['reorder_point' => 5])));

        Livewire::test(DatabaseNotifications::class)
            ->call('markAllNotificationsAsRead');

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }
}

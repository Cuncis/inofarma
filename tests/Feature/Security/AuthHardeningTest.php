<?php

namespace Tests\Feature\Security;

use App\Filament\Pages\MediaFiles;
use App\Models\Customer;
use App\Models\User;
use App\Support\Auth\SuperAdmin;
use App\Support\HomePageContent;
use App\Support\MediaLibrary;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

/**
 * Guessing limits on the 6-digit codes, and the other auth fixes from the
 * October 2026 security review. Repeated wrong guesses are simulated by hitting
 * the limiter directly rather than by chaining failing requests (see
 * `.ai/rules/feature.md`).
 */
class AuthHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_two_factor_challenge_stops_accepting_codes_after_five_wrong_guesses(): void
    {
        $secret = (new Google2FA)->generateSecretKey();
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dua-faktor'));

        foreach (range(1, 5) as $attempt) {
            RateLimiter::hit('admin-2fa:'.$user->id);
        }

        $this->post('/admin/dua-faktor', ['code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertSessionHasErrors('code');

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_a_phone_otp_is_used_up_after_five_wrong_guesses(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif']);
        $code = $customer->issuePhoneOtp();

        foreach (range(1, 5) as $attempt) {
            RateLimiter::hit('phone-otp:'.$customer->id);
        }

        $this->actingAs($customer, 'customer')
            ->post('/otp-code', ['code' => $code])
            ->assertSessionHasErrors('code');

        $customer->refresh();
        $this->assertNull($customer->phone_verified_at);
        $this->assertNull($customer->phone_otp_code);
    }

    public function test_changing_the_phone_number_clears_its_verification(): void
    {
        $customer = Customer::factory()->create(['status' => 'aktif', 'phone_verified_at' => now()]);

        $this->actingAs($customer, 'customer')
            ->post('/verify-phone', ['phone' => '081299990000'])
            ->assertSessionHasNoErrors();

        $customer->refresh();
        $this->assertSame('081299990000', $customer->phone);
        $this->assertNull($customer->phone_verified_at);
    }

    public function test_a_phone_number_already_on_another_account_is_refused(): void
    {
        Customer::factory()->create(['phone' => '081211112222']);
        $customer = Customer::factory()->create(['status' => 'aktif']);

        $this->actingAs($customer, 'customer')
            ->post('/verify-phone', ['phone' => '081211112222'])
            ->assertSessionHasErrors('phone');
    }

    public function test_only_super_admin_may_upload_new_svg_files(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $staff = User::factory()->create(['is_active' => true]);
        $staff->givePermissionTo(['Media:Lihat', 'Media:Unggah']);
        $this->actingAs($staff, 'web');

        $this->assertNotContains(SuperAdmin::SVG_TYPE, SuperAdmin::uploadTypes(MediaFiles::ACCEPTED_TYPES));
        $this->assertNotContains(SuperAdmin::SVG_TYPE, SuperAdmin::uploadTypes(HomePageContent::IMAGE_TYPES));

        Storage::fake('public');
        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')])
            ->call('saveDropped');
        $this->assertSame([], Storage::disk('public')->allFiles());

        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole(SuperAdmin::ROLE);
        $this->actingAs($superAdmin, 'web');

        $this->assertContains(SuperAdmin::SVG_TYPE, SuperAdmin::uploadTypes(MediaFiles::ACCEPTED_TYPES));

        Livewire::test(MediaFiles::class)
            ->set('dropped', [UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')])
            ->call('saveDropped');
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_a_media_upload_is_named_after_its_real_type_not_the_name_it_was_sent_with(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $path = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($path, $png.'<script>alert(document.domain)</script>');

        $name = MediaLibrary::storedName(new UploadedFile($path, 'promo.html', null, null, true));

        $this->assertMatchesRegularExpression('/^promo-[a-z0-9]{5}\.png$/', $name);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPhoneOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_correct_code_verifies_the_phone(): void
    {
        $customer = Customer::factory()->create();

        $code = $customer->issuePhoneOtp();

        $this->assertTrue($customer->verifyPhoneOtp($code));
        $this->assertNotNull($customer->phone_verified_at);
        $this->assertNull($customer->phone_otp_code);
    }

    public function test_the_wrong_code_is_rejected(): void
    {
        $customer = Customer::factory()->create();
        $customer->issuePhoneOtp();

        $this->assertFalse($customer->verifyPhoneOtp('000000'));
        $this->assertNull($customer->phone_verified_at);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        $customer = Customer::factory()->create();
        $code = $customer->issuePhoneOtp();

        $customer->forceFill(['phone_otp_expires_at' => now()->subMinute()])->save();

        $this->assertFalse($customer->verifyPhoneOtp($code));
    }

    public function test_a_code_cannot_be_reused(): void
    {
        $customer = Customer::factory()->create();
        $code = $customer->issuePhoneOtp();

        $this->assertTrue($customer->verifyPhoneOtp($code));
        $this->assertFalse($customer->verifyPhoneOtp($code));
    }
}

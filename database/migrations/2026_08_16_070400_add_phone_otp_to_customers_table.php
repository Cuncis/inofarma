<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storage for the phone verification OTP — hashed, never stored plain, same
 * reasoning as a password. There is no SMS gateway wired into this project
 * yet, so `ShopAuthController::sendPhoneOtp()` logs the code instead of
 * texting it; see that method's docblock for the production upgrade path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_otp_code')->nullable()->after('phone_verified_at');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['phone_otp_code', 'phone_otp_expires_at']);
        });
    }
};

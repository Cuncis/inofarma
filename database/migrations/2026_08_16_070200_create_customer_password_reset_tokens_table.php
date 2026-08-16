<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separate from `password_reset_tokens` (used by the `users` broker) so a staff
 * account and a customer account that happen to share an email address never
 * cross-redeem each other's reset link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
    }
};

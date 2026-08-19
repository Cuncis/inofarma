<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's standard `database` notification channel schema — what backs the
 * admin topbar's notification bell (Fase 8): new order at a branch, low
 * stock, a batch nearing expiry, a pickup nearing its deadline. Customer-
 * facing notifications (order confirmed, paid, shipped, ready, completed,
 * cancelled) go out over `mail`/`whatsapp` only and never land in this
 * table — a customer has no bell to read it from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();

            // The visitor's only credential: kept in their browser and sent in a
            // header, and put in the link of the reply email, so a chat can be
            // picked up again after the browser was closed. 40 random characters.
            $table->string('token', 40)->unique();

            $table->string('name', 120);
            $table->string('email', 190)->index();
            $table->enum('status', ['terbuka', 'ditutup'])->default('terbuka');

            // Visitor messages the admin has not seen yet. Drives the inbox badge.
            $table->unsignedSmallInteger('admin_unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable()->index();

            $table->timestamps();

            $table->index(['status', 'last_message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};

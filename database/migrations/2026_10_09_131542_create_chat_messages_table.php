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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender', ['pengunjung', 'admin']);

            // Which staff member wrote an admin reply. Null for the visitor, and
            // for a reply whose author was later removed.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->text('body');

            // Set when the visitor's open chat window received an admin reply.
            // A reply still empty here after a few minutes is emailed instead.
            $table->timestamp('visitor_seen_at')->nullable();
            $table->timestamp('emailed_at')->nullable();

            $table->timestamps();

            $table->index(['chat_conversation_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun pelanggan — terpisah dari `users`, yang menampung staf admin.
 *
 * Mencampur pelanggan dan staf dalam satu tabel adalah sumber celah keamanan
 * klasik: satu kesalahan pada guard dan pelanggan masuk ke panel admin.
 *
 * Jumlah pesanan dan total belanja TIDAK disimpan di sini — keduanya diturunkan
 * dari tabel `orders` supaya tidak pernah melenceng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_path')->nullable();

            // Cabang yang biasa dipakai — mempercepat kunjungan berikutnya.
            $table->foreignId('preferred_branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();

            $table->enum('status', ['aktif', 'nonaktif', 'diblokir'])->default('aktif');

            // Jejak persetujuan untuk UU PDP: kapan, versi kebijakan mana.
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->boolean('allows_location')->default(false);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

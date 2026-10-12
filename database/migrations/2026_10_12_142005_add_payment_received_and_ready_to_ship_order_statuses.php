<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Order statuses become explicit steps instead of one vague "diproses":
     *
     *   antar: menunggu pembayaran, pembayaran diterima, siap dikirim, dikirim, selesai
     *   ambil: menunggu pembayaran, pembayaran diterima, siap diambil, selesai
     *   (either one can end as dibatalkan, or kedaluwarsa when time ran out)
     *
     * Every old "diproses" order is a paid order waiting for the branch, which
     * is exactly "pembayaran diterima". The enum is widened first so the rows
     * can be moved, then narrowed to drop "diproses" (MySQL refuses an enum
     * that no longer contains a value some row still holds).
     */
    public function up(): void
    {
        $this->setStatuses(['menunggu pembayaran', 'diproses', 'pembayaran diterima', 'siap dikirim', 'siap diambil', 'dikirim', 'selesai', 'dibatalkan', 'kedaluwarsa']);

        DB::table('orders')->where('status', 'diproses')->update(['status' => 'pembayaran diterima']);

        $this->setStatuses(['menunggu pembayaran', 'pembayaran diterima', 'siap dikirim', 'siap diambil', 'dikirim', 'selesai', 'dibatalkan', 'kedaluwarsa']);
    }

    public function down(): void
    {
        $this->setStatuses(['menunggu pembayaran', 'diproses', 'pembayaran diterima', 'siap dikirim', 'siap diambil', 'dikirim', 'selesai', 'dibatalkan', 'kedaluwarsa']);

        DB::table('orders')->whereIn('status', ['pembayaran diterima', 'siap dikirim'])->update(['status' => 'diproses']);

        $this->setStatuses(['menunggu pembayaran', 'diproses', 'siap diambil', 'dikirim', 'selesai', 'dibatalkan', 'kedaluwarsa']);
    }

    /**
     * @param  list<string>  $statuses
     */
    private function setStatuses(array $statuses): void
    {
        Schema::table('orders', function (Blueprint $table) use ($statuses) {
            $table->enum('status', $statuses)->default('menunggu pembayaran')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Low stock alerts are switched off: reorder_point left the import and
     * the stock table, so nobody could change it any more. A reorder point of
     * 0 means "never alert". The column stays, so alerts can come back later.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branch_stocks')) {
            return;
        }

        DB::table('branch_stocks')->where('reorder_point', '!=', 0)->update(['reorder_point' => 0]);
    }

    public function down(): void {}
};

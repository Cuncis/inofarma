<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The pharmacy now classes products as 'bebas' or 'bebas terbatas' only.
        DB::table('products')->where('drug_class', 'non-obat')->update(['drug_class' => 'bebas']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Which products used to be 'non-obat' is not recorded, so this cannot be undone.
    }
};

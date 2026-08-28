<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indonesia's administrative regions (provinsi/kota-kabupaten/kecamatan/
 * kelurahan-desa) plus postal codes, for the cascading dropdowns on
 * `Shop/AddNewAddress.jsx`. Immutable reference data, populated by
 * `php artisan regions:import` from the bundled dumps under
 * `database/data/` — see that command's docblock for the source and how to
 * refresh it. Never written to at request time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            // Kepmendagri's own hierarchical code, e.g. `11.01.01.2001` —
            // used as the primary key rather than a surrogate id so a child
            // row's `parent_code` is just that same code with its last
            // dot-segment removed.
            $table->string('code', 13)->primary();
            $table->string('parent_code', 13)->nullable()->index();

            // 1 = provinsi, 2 = kota/kabupaten, 3 = kecamatan, 4 = kelurahan/desa.
            $table->unsignedTinyInteger('level');

            $table->string('name', 100);
            $table->string('postal_code', 5)->nullable();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};

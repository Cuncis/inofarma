<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 7.1: Biteship needs a box size, not just a weight, to quote a real
 * courier rate — `weight_grams` alone (Fase 4) was enough for the flat-fee
 * placeholder but understates cost for anything bulky.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedSmallInteger('length_cm')->default(0)->after('weight_grams');
            $table->unsignedSmallInteger('width_cm')->default(0)->after('length_cm');
            $table->unsignedSmallInteger('height_cm')->default(0)->after('width_cm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length_cm', 'width_cm', 'height_cm']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Columns people type to find a row, with their original varchar length.
     *
     * @var array<string, array<string, int>>
     */
    private const COLUMNS = [
        'users' => ['email' => 255],
        'customers' => ['email' => 255, 'code' => 20],
        'subscribers' => ['email' => 255],
        'products' => ['sku' => 40, 'slug' => 255],
        'branches' => ['code' => 20, 'slug' => 255, 'name' => 255],
        'suppliers' => ['code' => 20],
        'coupons' => ['code' => 40],
        'categories' => ['name' => 80, 'slug' => 80],
        'attributes' => ['name' => 80, 'slug' => 80],
        'stock_transfers' => ['code' => 30],
        'orders' => ['number' => 30],
        'inventory_batches' => ['batch_number' => 60],
    ];

    /**
     * MySQL compares these case-insensitively (its default collation), so
     * "Budi@Mail.com" logs in as "budi@mail.com" and SKU "prd-001" finds
     * "PRD-001". PostgreSQL's text is case-sensitive; its `citext` type gives
     * equality, LIKE, unique indexes and validation rules the same
     * case-insensitive behaviour without touching every query. MySQL already
     * works this way, and the SQLite test database is left as it is.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('create extension if not exists citext');

        foreach (self::COLUMNS as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                DB::statement("alter table {$table} alter column {$column} type citext");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $length) {
                DB::statement("alter table {$table} alter column {$column} type varchar({$length})");
            }
        }
    }
};

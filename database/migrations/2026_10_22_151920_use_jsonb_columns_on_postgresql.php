<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every JSON column, with the type `$table->json()` / `text()` gave it.
     *
     * @var array<string, array<string, string>>
     */
    private const COLUMNS = [
        'attributes' => ['values' => 'json'],
        'audit_logs' => ['old_values' => 'json', 'new_values' => 'json'],
        'branches' => ['operating_hours' => 'json'],
        'notifications' => ['data' => 'text'],
        'order_items' => ['batches_consumed' => 'json'],
        'payments' => ['raw_response' => 'json', 'raw_notification' => 'json'],
        'shipments' => ['raw_response' => 'json', 'history' => 'json'],
        'stock_transfers' => ['batches_shipped' => 'json'],
    ];

    /**
     * On PostgreSQL `$table->json()` is plain `json`, which has no equality
     * operator: a `select distinct branches.*` (the coupon form's branch
     * checklist) fails with "could not identify an equality operator for
     * type json". `jsonb` compares, sorts object keys and drops duplicate
     * keys, which is what MySQL's JSON type already does, so nothing reading
     * these columns sees a difference.
     *
     * `notifications.data` is Laravel's stock `text` column. Filament's
     * notification bell filters on `data->format`, which MySQL reads out of
     * text but PostgreSQL only out of json/jsonb ("operator does not exist:
     * text ->> unknown" on every admin page). Laravel always writes it as
     * encoded JSON, so every row converts.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                DB::statement("alter table {$table} alter column {$column} type jsonb using {$column}::jsonb");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $type) {
                DB::statement("alter table {$table} alter column {$column} type {$type} using {$column}::{$type}");
            }
        }
    }
};

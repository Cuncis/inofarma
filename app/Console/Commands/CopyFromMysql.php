<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * One-off move from MySQL to PostgreSQL: copies every row from the
 * `mysql_source` connection into the default connection, whose tables
 * `php artisan migrate` has already built.
 *
 * Everything runs in one transaction on the target, so a failure leaves it
 * exactly as it was. Each target table is emptied first, which also drops
 * the rows migrations insert on their own (the Inbox permissions), so the
 * copied ids line up with MySQL's. Tables are filled parents first, read
 * from the target's foreign keys; `categories.parent_id`, which points at
 * its own table, is filled in a second pass. MySQL's 0/1 booleans become
 * true/false, and each id counter is moved past the highest copied id so
 * the next order does not start again at 1.
 *
 * Sessions and cache are not copied: everyone logs in once more.
 *
 * Delete this command, and the `mysql_source` connection, once production
 * runs on PostgreSQL.
 */
class CopyFromMysql extends Command
{
    protected $signature = 'db:copy-from-mysql {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Salin semua data dari MySQL lama (mysql_source) ke basis data utama';

    public const SOURCE = 'mysql_source';

    /** Tables that are rebuilt by the app itself, never copied. */
    private const SKIPPED = ['migrations', 'sessions', 'cache', 'cache_locks'];

    private const CHUNK_SIZE = 500;

    public function handle(): int
    {
        $target = DB::connection();

        if ($target->getName() === self::SOURCE || $target->getDriverName() === 'mysql') {
            $this->error('Basis data utama masih MySQL. Arahkan DB_CONNECTION ke pgsql dulu.');

            return self::FAILURE;
        }

        $source = DB::connection(self::SOURCE);
        $tables = $this->tablesInCopyOrder($target);

        if (! $this->option('force') && ! $this->confirm("Semua isi {$target->getDatabaseName()} ({$target->getDriverName()}) akan dihapus lalu diisi dari MySQL. Lanjutkan?")) {
            return self::FAILURE;
        }

        $target->transaction(function () use ($source, $target, $tables) {
            foreach (array_reverse($tables) as $table) {
                $target->table($table)->truncate();
            }

            foreach ($tables as $table) {
                $this->copyTable($source, $target, $table);
            }

            if ($target->getDriverName() === 'pgsql') {
                $this->resetIdCounters($target, $tables);
            }
        });

        return $this->reportCounts($source, $target, $tables) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Every table except {@see SKIPPED}, each one after the tables its
     * foreign keys point to.
     *
     * @return list<string>
     */
    private function tablesInCopyOrder(Connection $target): array
    {
        $schema = $target->getSchemaBuilder();
        $tables = array_values(array_diff($schema->getTableListing(schemaQualified: false), self::SKIPPED));

        $parents = [];
        foreach ($tables as $table) {
            $parents[$table] = collect($schema->getForeignKeys($table))
                ->pluck('foreign_table')
                ->reject(fn (string $parent) => $parent === $table)
                ->unique()
                ->all();
        }

        $ordered = [];
        $visit = function (string $table) use (&$visit, &$ordered, $parents) {
            if (in_array($table, $ordered, true)) {
                return;
            }

            foreach ($parents[$table] ?? [] as $parent) {
                $visit($parent);
            }

            $ordered[] = $table;
        };

        foreach ($tables as $table) {
            $visit($table);
        }

        return $ordered;
    }

    private function copyTable(Connection $source, Connection $target, string $table): void
    {
        $schema = $target->getSchemaBuilder();
        $targetColumns = collect($schema->getColumns($table));
        $columns = array_values(array_intersect($targetColumns->pluck('name')->all(), $source->getSchemaBuilder()->getColumnListing($table)));
        $booleans = $targetColumns->whereIn('type_name', ['bool', 'boolean'])->pluck('name')->all();
        $selfReferences = collect($schema->getForeignKeys($table))
            ->where('foreign_table', $table)
            ->map(fn (array $foreignKey) => $foreignKey['columns'][0])
            ->values()
            ->all();
        $primaryKey = collect($schema->getIndexes($table))->firstWhere('primary', true)['columns'] ?? $columns;

        /** @var array<int|string, array<string, mixed>> $deferred */
        $deferred = [];
        $query = $source->table($table)->select($columns);

        foreach ($primaryKey as $column) {
            $query->orderBy($column);
        }

        $query->chunk(self::CHUNK_SIZE, function ($rows) use ($target, $table, $booleans, $selfReferences, &$deferred) {
            $target->table($table)->insert($rows->map(function (object $row) use ($booleans, $selfReferences, &$deferred) {
                $values = (array) $row;

                foreach ($booleans as $column) {
                    $values[$column] = $values[$column] === null ? null : (bool) $values[$column];
                }

                foreach ($selfReferences as $column) {
                    if ($values[$column] !== null) {
                        $deferred[$values['id']][$column] = $values[$column];
                        $values[$column] = null;
                    }
                }

                return $values;
            })->all());
        });

        foreach ($deferred as $id => $values) {
            $target->table($table)->where('id', $id)->update($values);
        }

        $this->line("  {$table}");
    }

    /**
     * PostgreSQL hands out ids from a sequence that copying explicit ids does
     * not advance; set each one so the next insert gets max(id) + 1.
     *
     * @param  list<string>  $tables
     */
    private function resetIdCounters(Connection $target, array $tables): void
    {
        $grammar = $target->getQueryGrammar();

        foreach ($tables as $table) {
            $column = collect($target->getSchemaBuilder()->getColumns($table))->firstWhere('auto_increment', true)['name'] ?? null;

            if ($column === null) {
                continue;
            }

            $target->select(
                "select setval(pg_get_serial_sequence(?, ?), coalesce(max({$grammar->wrap($column)}), 0) + 1, false) from {$grammar->wrapTable($table)}",
                [$table, $column],
            );
        }
    }

    /**
     * @param  list<string>  $tables
     */
    private function reportCounts(Connection $source, Connection $target, array $tables): bool
    {
        $rows = collect($tables)->map(fn (string $table) => [
            $table,
            $sourceCount = $source->table($table)->count(),
            $targetCount = $target->table($table)->count(),
            $sourceCount === $targetCount ? 'OK' : 'BEDA',
        ]);

        $this->table(['Tabel', 'MySQL', $target->getDriverName(), ''], $rows->all());

        $mismatches = $rows->where(3, 'BEDA')->count();

        if ($mismatches > 0) {
            $this->error("{$mismatches} tabel jumlah barisnya berbeda.");

            return false;
        }

        $this->info('Semua tabel tersalin dengan jumlah baris yang sama.');

        return true;
    }
}

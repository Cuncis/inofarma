<?php

namespace Tests\Feature;

use App\Console\Commands\CopyFromMysql;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * `db:copy-from-mysql` with a second in-memory SQLite database standing in
 * for MySQL. The PostgreSQL-only id counter reset is checked on the real run.
 */
class CopyFromMysqlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.'.CopyFromMysql::SOURCE => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        $this->artisan('migrate', ['--database' => CopyFromMysql::SOURCE])->assertSuccessful();
    }

    public function test_every_table_is_copied_with_its_ids_and_relations(): void
    {
        $this->insertIntoSource('branches', Branch::factory()->make(['id' => 5, 'supports_pickup' => false]));
        $this->insertIntoSource('suppliers', Supplier::factory()->make(['id' => 1]));
        // Foreign keys are checked per insert statement, so the parent must land
        // in a later chunk than its children for the order to matter.
        $this->insertIntoSource('categories', Category::factory()->make(['id' => 900]));
        foreach (range(1, 500) as $id) {
            $this->insertIntoSource('categories', Category::factory()->make(['id' => $id, 'parent_id' => 900]));
        }
        $this->insertIntoSource('products', Product::factory()->make(['id' => 7, 'category_id' => 2, 'supplier_id' => 1]));
        DB::connection(CopyFromMysql::SOURCE)->table('regions')->insert(['code' => '31', 'level' => 1, 'name' => 'DKI Jakarta']);

        $this->artisan('db:copy-from-mysql', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('branches', ['id' => 5, 'supports_pickup' => false, 'supports_delivery' => true]);
        $this->assertSame(500, DB::table('categories')->where('parent_id', 900)->count());
        $this->assertDatabaseHas('products', ['id' => 7, 'category_id' => 2, 'supplier_id' => 1]);
        $this->assertDatabaseHas('regions', ['code' => '31', 'name' => 'DKI Jakarta']);
    }

    public function test_rows_migrations_inserted_on_the_target_are_replaced_not_duplicated(): void
    {
        $sourcePermissions = DB::connection(CopyFromMysql::SOURCE)->table('permissions')->orderBy('id')->pluck('name', 'id')->all();
        $this->assertNotEmpty(DB::table('permissions')->count());

        $this->artisan('db:copy-from-mysql', ['--force' => true])->assertSuccessful();

        $this->assertSame($sourcePermissions, DB::table('permissions')->orderBy('id')->pluck('name', 'id')->all());
    }

    public function test_sessions_and_cache_are_left_alone(): void
    {
        DB::connection(CopyFromMysql::SOURCE)->table('sessions')->insert(['id' => 'source-session', 'payload' => '', 'last_activity' => 0]);
        DB::table('sessions')->insert(['id' => 'target-session', 'payload' => '', 'last_activity' => 0]);

        $this->artisan('db:copy-from-mysql', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('sessions', ['id' => 'target-session']);
        $this->assertDatabaseMissing('sessions', ['id' => 'source-session']);
    }

    public function test_declining_the_confirmation_changes_nothing(): void
    {
        $this->insertIntoSource('branches', Branch::factory()->make(['id' => 5]));

        $this->artisan('db:copy-from-mysql')
            ->expectsConfirmation('Semua isi '.DB::connection()->getDatabaseName().' ('.DB::connection()->getDriverName().') akan dihapus lalu diisi dari MySQL. Lanjutkan?', 'no')
            ->assertFailed();

        $this->assertDatabaseMissing('branches', ['id' => 5]);
    }

    public function test_it_refuses_to_copy_into_the_source_itself(): void
    {
        $default = config('database.default');
        config(['database.default' => CopyFromMysql::SOURCE]);

        try {
            $this->artisan('db:copy-from-mysql', ['--force' => true])
                ->expectsOutputToContain('Basis data utama masih MySQL')
                ->assertFailed();
        } finally {
            // RefreshDatabase rolls back whichever connection is the default at
            // tear-down; leaving the source there leaves the real test
            // transaction open and breaks every test class that runs after this one.
            config(['database.default' => $default]);
        }
    }

    private function insertIntoSource(string $table, object $model): void
    {
        DB::connection(CopyFromMysql::SOURCE)->table($table)->insert($model->getAttributes());
    }
}

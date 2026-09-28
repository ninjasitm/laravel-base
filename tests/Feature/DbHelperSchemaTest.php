<?php

namespace Tests\Feature;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nitm\Helpers\DbHelper;
use Orchestra\Testbench\TestCase;

class DbHelperSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        config()->set('cache.default', 'array');
        DB::purge('sqlite');
        Cache::flush();

        Schema::create('helper_parents', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->unique('label', 'helper_parents_label_unique');
        });
        Schema::create('helper_children', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('helper_parent_id')->constrained('helper_parents');
            $table->string('note')->nullable();
            $table->index('note', 'helper_children_note_index');
        });
    }

    public function test_tables_and_names_are_independently_cached_and_keep_table_metadata(): void
    {
        $tables = DbHelper::getTables();
        $this->assertInstanceOf(Collection::class, $tables);
        $this->assertContains('helper_children', $tables->map(fn ($table) => $table->getName())->all());
        $this->assertContains('helper_parents', $tables->map(fn ($table) => $table->getName())->all());
        $this->assertNotEmpty($tables->firstWhere('name', 'helper_children')->schema_qualified_name);
        $key = 'tables-sqlite-:memory:-';
        $this->assertIsString(serialize(Cache::get($key)));

        $names = DbHelper::getTableNames();
        $this->assertInstanceOf(Collection::class, $names);
        $this->assertContains('helper_children', $names->all());
        $this->assertContains('helper_parents', $names->all());
        $this->assertSame($names->all(), DbHelper::getTableNames()->all());
        $this->assertContains('helper_children', DbHelper::getTables()->map(fn ($table) => $table->getName())->all());

        Cache::flush();
        $this->assertContains('helper_children', DbHelper::getTableNames()->all());
        $this->assertContains('helper_children', DbHelper::getTables()->map(fn ($table) => $table->getName())->all());
    }

    public function test_columns_fields_and_indexes_keep_the_metadata_consumers_need(): void
    {
        $columns = DbHelper::getColumns('helper_children');
        $this->assertInstanceOf(Collection::class, $columns);
        $this->assertTrue($columns->has('note'));
        $this->assertSame('note', $columns->first(fn ($column) => $column->getName() === 'note')->name);
        $this->assertFalse($columns->first(fn ($column) => $column->getName() === 'note')->getNotnull());
        $this->assertTrue($columns->first(fn ($column) => $column->getName() === 'helper_parent_id')->getNotnull());
        $this->assertNotEmpty($columns->first(fn ($column) => $column->getName() === 'note')->type_name);
        $this->assertContains('note', DbHelper::getFields('helper_children')->pluck('name')->all());
        $required = $columns->filter(fn ($column) => $column->getNotnull())
            ->filter(fn ($column) => $column->getName() !== 'id')->pluck('name')->all();
        $this->assertContains('helper_parent_id', $required);
        $this->assertNotContains('note', $required);

        $indexes = DbHelper::getIndexes('helper_children');
        $this->assertInstanceOf(Collection::class, $indexes);
        $this->assertTrue($indexes->has('helper_children_note_index'));
        $index = $indexes->first(fn ($entry) => $entry->getName() === 'helper_children_note_index');
        $this->assertSame(['note'], $index->getColumns());
        $this->assertFalse($index->isUnique());
        $this->assertTrue($indexes->contains(fn ($entry) => $entry->isPrimary()));
    }

    public function test_foreign_constraints_names_and_column_presence(): void
    {
        $constraints = DbHelper::getForeignConstraints('helper_children');
        $this->assertInstanceOf(Collection::class, $constraints);
        $this->assertCount(1, $constraints);
        $this->assertSame('helper_children_helper_parent_id_foreign', $constraints->first()->getName());
        $this->assertSame(['helper_parent_id'], $constraints->first()->getColumns());
        $this->assertSame('helper_parents', $constraints->first()->foreign_table);
        $this->assertSame(['helper_children_helper_parent_id_foreign'], DbHelper::getForeignConstraintNames('helper_children')->all());
        $this->assertTrue(DbHelper::hasForeignConstraint('helper_children', 'helper_children_helper_parent_id_foreign'));
        $this->assertFalse(DbHelper::hasForeignConstraint('helper_children', 'other_foreign'));
        $this->assertTrue(DbHelper::hasForeignConstraintColumns('helper_children', 'helper_parent_id'));
        $this->assertTrue(DbHelper::hasForeignConstraintColumns('helper_children', ['helper_parent_id']));
        $this->assertFalse(DbHelper::hasForeignConstraintColumns('helper_children', 'note'));
        $this->assertFalse(DbHelper::hasForeignConstraintColumns('helper_children', ['helper_parent_id', 'note']));
    }

    public function test_postgresql_table_names_retain_distinct_schema_identity(): void
    {
        // Exercise Laravel's real Postgres schema grammar, builder, and result processor;
        // only the database response is simulated when no PostgreSQL credentials are available.
        $connection = new class(null, 'app_database', '', ['driver' => 'pgsql', 'name' => 'pgsql']) extends PostgresConnection
        {
            public function selectFromWriteConnection($query, $bindings = [])
            {
                if (! str_contains($query, 'pg_class')) {
                    throw new \LogicException('Expected a PostgreSQL table discovery query');
                }

                return [
                    (object) ['name' => 'people', 'schema' => 'public', 'size' => 0, 'comment' => null],
                    (object) ['name' => 'people', 'schema' => 'tenant', 'size' => 0, 'comment' => null],
                ];
            }
        };
        DB::shouldReceive('connection')->andReturn($connection);

        $this->assertSame(['public.people', 'tenant.people'], DbHelper::getTableNames('app_database')->all());
        $this->assertSame(['public.people', 'tenant.people'], DbHelper::getTables('app_database')->pluck('schema_qualified_name')->all());
    }

    public function test_mysql_table_names_remain_unqualified(): void
    {
        $connection = new class(null, 'app_database', '', ['driver' => 'mysql', 'name' => 'mysql']) extends MySqlConnection
        {
            public function selectFromWriteConnection($query, $bindings = [])
            {
                if (! str_contains($query, 'information_schema.tables')) {
                    throw new \LogicException('Expected a MySQL table discovery query');
                }

                return [(object) ['name' => 'people', 'schema' => 'app_database']];
            }
        };
        DB::shouldReceive('connection')->andReturn($connection);

        $this->assertSame(['people'], DbHelper::getTableNames('app_database')->all());
    }
}

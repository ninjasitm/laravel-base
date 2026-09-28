<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nitm\Content\Traits\FiltersModels;
use Nitm\Content\Traits\Model as ModelTrait;
use Nitm\Content\Traits\Search;
use Orchestra\Testbench\TestCase;

class SchemaIntrospectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        DB::purge('sqlite');

        Schema::create('schema_items', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->boolean('active');
        });
        Schema::create('schema_item_children', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schema_item_id')->constrained('schema_items');
        });
    }

    private function model(): EloquentModel
    {
        return new class extends EloquentModel
        {
            use FiltersModels;
            use ModelTrait;
            use Search;

            protected $table = 'schema_items';

            public $timestamps = false;
        };
    }

    public function test_columns_keep_name_keys_and_search_type_accessors(): void
    {
        $model = $this->model();
        $columns = $model->getTableColumns();

        $this->assertSame('title', $columns['title']->getName());
        $this->assertSame('string', $columns['title']->getType()->getName());
        $this->assertSame('text', $columns['description']->getType()->getName());
        $this->assertSame('integer', $columns['quantity']->getType()->getName());
        $this->assertSame('decimal', $columns['price']->getType()->getName());
        $this->assertSame('boolean', $columns['active']->getType()->getName());
        $this->assertTrue($model->hasColumn('title'));
        $this->assertFalse($model->hasColumn('missing'));
        $this->assertSame(array_keys($columns), $model->getTableColumnsAsCollection()->keys()->all());
    }

    public function test_foreign_key_names_and_column_lookup_work_without_doctrine(): void
    {
        $model = $this->model();
        $this->assertContains('schema_item_children_schema_item_id_foreign', $model->getTableForeignKeys('schema_item_children'));
        $this->assertTrue($model->hasForeignKey('schema_item_children', 'schema_item_id'));
        $this->assertFalse($model->hasForeignKey('schema_item_children', 'missing'));
    }

    public function test_postgresql_native_metadata_maps_to_legacy_search_types_and_names(): void
    {
        $schema = new class
        {
            public function getColumns(string $table): array
            {
                return [
                    ['name' => 'id', 'type_name' => 'int8', 'type' => 'bigint'],
                    ['name' => 'title', 'type_name' => 'varchar', 'type' => 'character varying(255)'],
                    ['name' => 'quantity', 'type_name' => 'int4', 'type' => 'integer'],
                    ['name' => 'active', 'type_name' => 'bool', 'type' => 'boolean'],
                    ['name' => 'price', 'type_name' => 'numeric', 'type' => 'numeric(8,2)'],
                ];
            }

            public function getForeignKeys(string $table): array
            {
                return [['name' => 'schema_item_children_schema_item_id_foreign', 'columns' => ['schema_item_id']]];
            }
        };
        $connection = new class($schema)
        {
            public function __construct(private object $schema) {}

            public function getName(): string
            {
                return 'pgsql';
            }

            public function getDatabaseName(): string
            {
                return 'native_metadata_test';
            }

            public function getSchemaBuilder(): object
            {
                return $this->schema;
            }
        };
        $model = new class($connection) extends EloquentModel
        {
            use ModelTrait;

            protected $table = 'schema_items';

            public function __construct(private ?object $schemaConnection = null)
            {
                parent::__construct();
            }

            public function getConnection(): object
            {
                return $this->schemaConnection ?? parent::getConnection();
            }
        };

        $columns = $model->getTableColumns();
        $this->assertSame('bigint', $columns['id']->getType()->getName());
        $this->assertSame('string', $columns['title']->getType()->getName());
        $this->assertSame('integer', $columns['quantity']->getType()->getName());
        $this->assertSame('boolean', $columns['active']->getType()->getName());
        $this->assertSame('decimal', $columns['price']->getType()->getName());
        $this->assertSame(['schema_item_children_schema_item_id_foreign'], $model->getTableForeignKeys('schema_item_children'));
    }

    public function test_empty_string_and_numeric_searches_preserve_results(): void
    {
        DB::table('schema_items')->insert([
            ['title' => 'Alpha', 'description' => 'first', 'quantity' => 9, 'price' => 1.25, 'active' => true],
            ['title' => 'Beta', 'description' => 'second', 'quantity' => 42, 'price' => 2.50, 'active' => false],
        ]);

        $model = $this->model();
        $this->assertSame(2, $model->newQuery()->search([])->count());
        $this->assertSame(['Alpha'], $model->newQuery()->search(['s' => 'Alpha'])->pluck('title')->all());
        $this->assertSame(['Beta'], $model->newQuery()->search(['s' => 'second'])->pluck('title')->all());
        $this->assertSame(['Beta'], $model->newQuery()->search(['s' => '42'])->pluck('title')->all());
        $this->assertSame(['Beta'], $model->newQuery()->search(['filter' => ['title' => 'Beta']])->pluck('title')->all());
        $this->assertSame(['Beta'], $model->newQuery()->search(['filter' => ['quantity' => 42]])->pluck('title')->all());
    }
}

<?php

namespace Nitm\Testing;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    public static $databaseSeeded = false;

    /**
     * @var App\Team
     */
    protected $team;

    protected function setupTeam($team)
    {
        $this->team = $team;
    }


    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        if (class_exists('Laravel\Cashier\Cashier')) {
            \Laravel\Cashier\Cashier::ignoreMigrations();
        }
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Hotfix for SQLite
     */
    public function hotfixSqlite()
    {
        \Illuminate\Database\Connection::resolverFor(
            'sqlite',
            function ($connection, $database, $prefix, $config) {
                return new class($connection, $database, $prefix, $config) extends SQLiteConnection
                {
                    public function getSchemaBuilder()
                    {
                        if ($this->schemaGrammar === null) {
                            $this->useDefaultSchemaGrammar();
                        }
                        return new class($this) extends SQLiteBuilder
                        {
                            protected function createBlueprint($table, \Closure $callback = null)
                            {
                                return new class($table, $callback) extends Blueprint
                                {
                                    public function dropForeign($index)
                                    {
                                        return new Fluent();
                                    }
                                };
                            }
                        };
                    }
                };
            }
        );
    }

    /**
     * Generate models from a factory with some common options
     *
     * @param  string  $class
     * @param  mixed   $options [states => states]
     * @param  integer $count
     * @return Factory
     */
    protected function generateModels(string $class, $options = null, int $count = 3)
    {
        $factory = factory($class, $count);
        if (is_array($options)) {
            if ($states = Arr::get($options, 'states')) {
                call_user_func_array([$factory, 'states'], (array)$states);
            }
        }
        return $factory;
    }

    /**
     * Generate models from a factory with some common options
     *
     * @param  string  $class
     * @param  mixed   $options [states => states]
     * @param  integer $count
     * @return Factory
     */
    protected function generateModel(string $class, $options = null)
    {
        $factory = factory($class, $count);
        if (is_array($options)) {
            if ($states = Arr::get($options, 'states')) {
                call_user_func_array([$factory, 'states'], (array)$states);
            }
        }
        return $factory;
    }

    /**
     * Get the relation for a factory generator
     *
     * @param  string|array $from
     * @return string
     */
    protected function getFactoryRelation($from)
    {
        return is_array($from) ? Arr::get($from, 'relation') : $from;
    }

    protected function getFactoryAndRelation($class, $options, $count = null)
    {
        return [
            is_null($count) ? $this->generateModel($class, $options) : $this->generateModels($class, $options, $count),
            $this->getFactoryRelation($options)
        ];
    }
}
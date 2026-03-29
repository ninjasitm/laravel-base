<?php
namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

abstract class TestCase extends BaseTestCase {
    public static $databaseSeeded = false;

    protected $apiBase = '/api/teams';

    protected $usesTeams = false;

    use CreatesApplication, RefreshDatabase, WithoutMiddleware;
    /**
     * @var App\Models\Team
     */
    protected $team;

    /**
     * Summary of __construct
     * @param mixed $name
     * @param iterable$data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '') {
        $cashierClass = 'Laravel\\Cashier\\Cashier';
        if (class_exists($cashierClass)) {
            $cashierClass::ignoreMigrations();
        }
        parent::__construct($name, $data, $dataName);
        // $this->hotfixSqlite();
    }

    /**
     * Summary of setupTeam
     * @param mixed $team
     * @return void
     */
    protected function setupTeam($team) {
        $this->team = $team;
    }

    /**
     * Summary of hotfixSqlite
     * @return void
     */
    public function hotfixSqlite() {
        \Illuminate\Database\Connection::resolverFor(
            'sqlite',
            function ($connection, $database, $prefix, $config) {
                return new class($connection, $database, $prefix, $config) extends SQLiteConnection {
                    public function getSchemaBuilder() {
                        if ($this->schemaGrammar === null) {
                            $this->useDefaultSchemaGrammar();
                        }
                        return new class($this) extends SQLiteBuilder {
                            protected function createBlueprint($table,  ? \Closure $callback = null) {
                                return new class($table, $callback) extends Blueprint {
                                    public function dropForeign($index) {
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
     * Apply legacy factory options to a modern Eloquent factory instance.
     *
     * @param mixed $factory
     * @param mixed $options
     * @return mixed
     */
    protected function applyFactoryOptions($factory, $options = null) {
        if (! is_array($options)) {
            return $factory;
        }

        foreach ((array) Arr::get($options, 'states', []) as $state) {
            if (is_string($state) && method_exists($factory, $state)) {
                $factory = $factory->{$state}();
                continue;
            }

            if (is_array($state) || is_callable($state)) {
                $factory = $factory->state($state);
            }
        }

        return $factory;
    }

    /**
     * Generate models from a factory with some common options
     *
     * @param string  $class
     * @param mixed   $options [states => states]
     * @param integer $count
     * @return mixed
     */
    protected function generateModels(string $class, $options = null, int $count = 3) {
        return $this->applyFactoryOptions($class::factory()->count($count), $options);
    }

    /**
     * Generate models from a factory with some common options
     *
     * @param string  $class
     * @param mixed   $options [states => states]
     * @param integer $count
     * @return mixed
     */
    protected function generateModel(string $class, $options = null) {
        return $this->applyFactoryOptions($class::factory(), $options);
    }

    /**
     * Get the relation for a factory generator
     *
     * @param string|array $from
     * @return string
     */
    protected function getFactoryRelation($from) {
        return is_array($from) ? Arr::get($from, 'relation') : $from;
    }

    protected function getFactoryAndRelation($class, $options, $count = null) {
        return [
            is_null($count) ? $this->generateModel($class, $options) : $this->generateModels($class, $options, $count),
            $this->getFactoryRelation($options),
        ];
    }
}

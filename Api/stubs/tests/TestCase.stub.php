<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use ArraySubsetAsserts;

    public static $databaseSeeded = false;

    protected $apiBase = '/api/teams';

    protected $usesTeams = true;

    use CreatesApplication, RefreshDatabase, WithoutMiddleware;
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
        if(class_exists('Laravel\Cashier\Cashier')) {
            \Laravel\Cashier\Cashier::ignoreMigrations();
        }
        parent::__construct($name, $data, $dataName);
        // $this->hotfixSqlite();
    }
    // public function setUpTraits(): void
    // {
    //     parent::setUpTraits();

    //     unset($this->app['middleware.disable']);
    //     $this->withoutMiddleware([
    //         'auth', 'auth.basic', 'resolvesTeam',
    //         \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    //         \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    //         \App\Http\Middleware\TrimStrings::class,
    //         \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    //         \App\Http\Middleware\TrustProxies::class,
    //         \Barryvdh\Cors\HandleCors::class,
    //         \App\Http\Middleware\FrameHeadersMiddleware::class,
    //         'auth',
    //         'auth.basic',
    //         'cache.headers',
    //         'can',
    //         'dev',
    //         'guest',
    //         'hasTeam',
    //         'throttle',
    //         'signed',
    //         'subscribed',
    //         'teamSubscribed',
    //         'isAdmin',
    //         'isMe',
    //         'isMentor',
    //         'isMentorOrAdmin',
    //         'isStudent',
    //         'isStudentOrMentor',
    //         'isTeamMember',
    //         'canSubmitData',
    //         'resolvesTeam',
    //         'fw-only-whitelisted',
    //         'fw-block-blacklisted',
    //         'fw-block-attacks'
    //     ]);
    // }

    /**
     *
     */
    public function hotfixSqlite()
    {
        \Illuminate\Database\Connection::resolverFor(
            'sqlite', function ($connection, $database, $prefix, $config) {
                return new class ($connection, $database, $prefix, $config) extends SQLiteConnection
            {
                    public function getSchemaBuilder()
                    {
                        if ($this->schemaGrammar === null) {
                            $this->useDefaultSchemaGrammar();
                        }
                        return new class ($this) extends SQLiteBuilder
                    {
                            protected function createBlueprint($table, \Closure $callback = null)
                            {
                                return new class ($table, $callback) extends Blueprint
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
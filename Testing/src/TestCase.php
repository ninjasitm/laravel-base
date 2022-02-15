<?php

namespace Nitm\Testing;

use Illuminate\Support\Arr;
use Nitm\Content\Models\Team;
use Nitm\Content\Models\User;
use Illuminate\Support\Fluent;
use Nitm\Content\Models\TeamUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Nitm\Content\NitmContentServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use ArraySubsetAsserts, RefreshDatabase, CreatesApplication;

    public static $databaseSeeded = false;

    /**
     * @var Nitm\Content\Team
     */
    protected $team;

    protected function setupTeam($team)
    {
        $this->team = $team;
    }

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        ini_set('memory_limit', '2G');
        if (class_exists('Laravel\Cashier\Cashier')) {
            \Laravel\Cashier\Cashier::ignoreMigrations();
        }
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Use as the given role for the specified team
     *
     * @param  mixed $role
     * @param  mixed $team
     * @return void
     */
    protected function useAs($role, $team = null)
    {
        $team = $team ?: $this->team ?: Team::factory()->create();
        $class = NitmContentServiceProvider::userModel();
        $user = $class::factory()->create();
        $teamUser = TeamUser::firstOrCreate(['team_id' => $team->id, 'role' => $role, 'user_id' => $user->id, 'is_approved' => true]);
        auth()->login($user);
        return $user;
    }

    /**
     * useAsUser
     *
     * @param  mixed $team
     * @return void
     */
    protected function useAsUser($team = null)
    {
        return $this->useAs(User::ROLE_USER, $team);
    }

    /**
     * useAsAdmin
     *
     * @param  mixed $team
     * @return void
     */
    protected function useAsAdmin($team = null)
    {
        return $this->useAs(User::ROLE_ADMIN, $team);
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
                call_user_func_array([$factory, 'states'], (array) $states);
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
                call_user_func_array([$factory, 'states'], (array) $states);
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
            $this->getFactoryRelation($options),
        ];
    }

    public function setUpTraits(): void
    {
        parent::setUpTraits();

        unset($this->app['middleware.disable']);
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            // 'auth',
            'auth.basic',
            'cache.headers',
            'can',
            'dev',
            'guest',
            'throttle',
            'signed',
            'fw-only-whitelisted',
            'fw-block-blacklisted',
            'fw-block-attacks'
        ]);
    }
}
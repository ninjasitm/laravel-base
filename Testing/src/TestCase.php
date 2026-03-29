<?php
namespace Nitm\Testing;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Nitm\Content\Models\Team;
use Nitm\Content\Models\TeamUser;
use Nitm\Content\Models\User;
use Nitm\Content\NitmContent;

abstract class TestCase extends BaseTestCase {
    use RefreshDatabase;
    use \Nitm\Testing\CreatesApplication;

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
     * Summary of response
     * @var \Illuminate\Testing\TestResponse
     */
    protected $response;

    /**
     * Indicates if the database has been seeded.
     * @var bool
     */
    public static $databaseSeeded = false;

    /**
     * @var Nitm\Content\Team
     */
    protected $team;

    protected function setupTeam($team) {
        $this->team = $team;
    }

    protected function setUp(): void {
        ini_set('memory_limit', '2G');
        $cashierClass = 'Laravel\\Cashier\\Cashier';
        if (class_exists($cashierClass)) {
            $cashierClass::ignoreMigrations();
        }
        parent::setUp();
    }

    protected function assertArraySubset(array $subset, array $array, bool $strict = false, string $message = ''): void {
        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $array, $message ?: "Failed asserting that key [{$key}] exists.");

            if (is_array($value)) {
                $this->assertIsArray($array[$key], $message ?: "Failed asserting that key [{$key}] is an array.");
                $this->assertArraySubset($value, $array[$key], $strict, $message);
                continue;
            }

            if ($strict) {
                $this->assertSame($value, $array[$key], $message);
            } else {
                $this->assertEquals($value, $array[$key], $message);
            }
        }
    }

    /**
     * Use as the given role for the specified team
     *
     * @param mixed $role
     * @param mixed $team
     * @return void
     */
    protected function useAs($role, $team = null) {
        $team     = $team ?: $this->team ?: Team::factory()->create();
        $class    = NitmContent::userModel();
        $user     = $class::factory()->create();
        $teamUser = TeamUser::firstOrCreate(['team_id' => $team->id, 'role' => $role, 'user_id' => $user->id, 'is_approved' => true]);
        $this->actingAs($user);
        return $user;
    }

    /**
     * useAsUser
     *
     * @param mixed $team
     * @return void
     */
    protected function useAsUser($team = null) {
        return $this->useAs(User::ROLE_USER, $team);
    }

    /**
     * useAsAdmin
     *
     * @param mixed $team
     * @return void
     */
    protected function useAsAdmin($team = null) {
        return $this->useAs(User::ROLE_ADMIN, $team);
    }

    /**
     * Hotfix for SQLite
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

    public function setUpTraits() : void {
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
            'fw-block-attacks',
        ]);
    }
}

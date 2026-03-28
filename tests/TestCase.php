<?php
namespace Tests;

use function Orchestra\Testbench\artisan;
use Nitm\Content\Models\User;
use Nitm\Content\NitmContent;
use Nitm\Content\NitmContentServiceProvider;
use Nitm\Testing\ApiTestTrait;
use Nitm\Testing\PackageTestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    use ApiTestTrait;
    private $dbMigrated = false;

    protected function getPackageProviders($app) {
        return [
            NitmContentServiceProvider::class,
        ];
    }
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app) {
        NitmContent::useUserModel(User::class);
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql', [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => env('DB_USERNAME', 'testing_user'),
            'password' => env('DB_PASSWORD', 'testing'),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ]);
    }
    protected function defineDatabaseMigrations() {
        if (! $this->dbMigrated) {
            $this->artisan('migrate:fresh', ['--database' => 'pgsql']);
            $this->dbMigrated = true;

            $this->beforeApplicationDestroyed(
                fn() => artisan($this, 'migrate:rollback', ['--database' => 'pgsql'])
            );
        }
    }
}
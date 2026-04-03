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
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
    protected function defineDatabaseMigrations() {
        if (! $this->dbMigrated) {
            $this->artisan('migrate:fresh', ['--database' => 'sqlite']);
            $this->dbMigrated = true;

            $this->beforeApplicationDestroyed(
                fn() => artisan($this, 'migrate:rollback', ['--database' => 'sqlite'])
            );
        }
    }
}
<?php

namespace Tests;

use function Orchestra\Testbench\artisan;
use Nitm\Content\Models\User;
use Nitm\Content\NitmContent;
use Nitm\Content\NitmContentServiceProvider;
use Nitm\Testing\ApiTestTrait;
use Nitm\Testing\PackageTestCase as BaseTestCase;
use Orchestra\Testbench\Attributes\WithMigration;
use Nitm\Testing\RefreshDatabase;
abstract class TestCase extends BaseTestCase
{
    use ApiTestTrait;
    private $dbMigrated = false;

    protected function getPackageProviders($app)
    {
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
    protected function defineEnvironment($app)
    {
        NitmContent::useUserModel(User::class);
        $app['config']->set('database.default', 'testing');
    }
    protected function defineDatabaseMigrations()
    {
        // $this->loadMigrationsFrom(
        //     [__DIR__ . '/../Content/src/Database/migrations']
        // );


        if (!$this->dbMigrated) {
            $this->artisan('migrate', ['--database' => 'testing']);
            $this->dbMigrated = true;

            $this->beforeApplicationDestroyed(
                fn() => artisan($this, 'migrate:rollback', ['--database' => 'testing'])
            );
        }
    }
}
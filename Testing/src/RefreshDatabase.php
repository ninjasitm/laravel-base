<?php

namespace Nitm\Testing;

use Illuminate\Contracts\Console\Kernel;

trait RefreshDatabase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        $connection = $this->app->make('em')->getConnection();
        $connection->beginTransaction();

        $this->beforeApplicationDestroyed(
            function () use ($connection) {
                $connection->rollBack();
            }
        );
    }

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshInMemoryDatabase()
    {
        if (!app()->environment('testing')) {
            throw new \Exception("Not in testing environment!");
        }

        $this->artisan('migrate:refresh');

        $this->app[Kernel::class]->setArtisan(null);
    }
}
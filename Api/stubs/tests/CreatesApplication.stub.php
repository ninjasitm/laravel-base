<?php

namespace Tests;

use Artisan;
use App\User;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = include __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp(): void
    {
        parent::setUp();
        // Artisan::call('migrate:refresh');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        // Artisan::call('migrate');
        Artisan::call('db:seed');
        if($this->usesTeams) {
            $this->setupTeam(factory(\App\Team::class)->create());
        }

        auth()->login(User::first());
    }

    public function tearDown(): void
    {
        // Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
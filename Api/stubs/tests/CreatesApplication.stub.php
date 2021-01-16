<?php

namespace Tests;

use Artisan;
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
        $teamClass = config('nitm-api.team_model') ?? config('nitm-content.team_model');
        if($this->usesTeams && class_exists($teamClass)) {
            $this->setupTeam(factory($teamClass)->create());
        }

        $userClass = config('nitm-api.user_model') ?? config('nitm-content.user_model');
        auth()->login($userClass::first());
    }

    public function tearDown(): void
    {
        // Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
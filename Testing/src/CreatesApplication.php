<?php

namespace Nitm\Testing;

use Artisan;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Optional providers to register
     */
    protected static $registerProviders = [];
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = include $_SERVER['PWD'] . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Register Service Providers
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        // Needed for team tests
        foreach (static::$registerProviders as $provider) {
            if (class_exists($provider)) {
                app()->register($provider);
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('db:seed');

        $this->registerServiceProviders();

        $teamClass = config('nitm-content.team_model');
        if (property_exists($this, 'usesTeams') && $this->usesTeams && class_exists($teamClass)) {
            $this->setupTeam($teamClass::factory()->create());
        }

        $userClass = config('nitm-content.user_model');
        auth()->login($userClass::first());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        // Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
<?php

namespace Nitm\Testing;

use Artisan;
use Illuminate\Contracts\Console\Kernel;
use App\Providers\JetstreamServiceProvider;

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

    /**
     * registerServiceProviders
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        // Needed for team tests
        app()->register(JetstreamServiceProvider::class);
    }

    public function setUp(): void
    {
        parent::setUp();
        // Artisan::call('migrate:refresh');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        // Artisan::call('migrate');
        Artisan::call('db:seed');

        $this->registerServiceProviders();

        $teamClass = config('nitm-content.team_model');
        if ($this->usesTeams && class_exists($teamClass)) {
            $this->setupTeam(factory($teamClass)->create());
        }

        $userClass = config('nitm-content.user_model');
        auth()->login($userClass::first());
    }

    public function tearDown(): void
    {
        // Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
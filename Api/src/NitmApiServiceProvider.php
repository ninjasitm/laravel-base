<?php

namespace Nitm\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Nitm Api Service Provider
 */
class NitmApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerRoutes();
        // $this->registerMigrations();
        $this->registerPublishing();

        // $this->loadViewsFrom(
        //     __DIR__ . '/../publishes/resources/views',
        //     'nitm-api'
        // );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group(
            $this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
            }
        );
    }

    /**
     * Get the Telescope route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => config('nitm-api.domain', null),
            'namespace' => 'Nitm\Content\Http\Controllers',
            'prefix' => config('nitm-api.path', 'api'),
            'middleware' => 'nitm-api',
        ];
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            // $this->publishes(
            //     [
            //     __DIR__ . '/Database/migrations' => database_path('migrations'),
            //     ], 'nitm-api-migrations'
            // );

            $this->publishes(
                [
                __DIR__ . '/../publishes/resources/infyom' => resource_path('infyom'),
                ], 'nitm-api-infyom'
            );

            $this->publishes(
                [
                __DIR__ . '/../publishes/config/nitm-api.php' => config_path('nitm-api.php'),
                ], 'nitm-api-config'
            );

            $this->publishes(
                [
                    __DIR__.'/../stubs/ApiController.stub.php' => app_path('Http/Controllers/API/ApiController.php'),
                    __DIR__.'/../stubs/Controller.stub.php' => app_path('Http/Controllers/Controller.php'),
                    __DIR__.'/../stubs/TeamApiController.stub.php' => app_path('Http/Controllers/API/TeamApiController.php'),
                    __DIR__.'/../stubs/TeamController.stub.php' => app_path('Http/Controllers/TeamController.php'),
                    __DIR__ . '/../stubs/tests/ApiTestTrait.stub.php' => app_path('tests/ApiTestTrait.php'),
                    __DIR__ . '/../stubs/tests/CreatesApplication.stub.php' => app_path('tests/CreatesApplication.php'),
                    __DIR__ . '/../stubs/tests/RefreshDatabase.stub.php' => app_path('tests/RefreshDatabase.php'),
                    __DIR__ . '/../stubs/tests/TestCase.stub.php' => app_path('tests/TestCase.php'),
                ], 'nitm-api'
            );
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nitm-api.php',
            'nitm-api'
        );

        // app()->singleton(
        //     'nitm.api', function () {
        //         return Rest::instance();
        //     }
        // );

        // Register clearlogs console command
        // TODO: This functionality is not finished yet
        // $this->registerConsoleCommand('nitm.api-clearlogs', 'Nitm\Api\Console\ClearLogs');
    }
}
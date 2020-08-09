<?php

namespace Nitm\Api;

use Illuminate\Foundation\AliasLoader;
use Nitm\Api\Models\Configs as RestfulConfig;
use Nitm\Api\Classes\Rest;
use Nitm\Api\Classes\Trivet;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NitmApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__ . '/../publishes/resources/views',
            'nitm-api'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
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
            $this->publishes([
                __DIR__ . '/Database/migrations' => database_path('migrations'),
            ], 'nitm-api-migrations');

            // $this->publishes([
            //     __DIR__ . '/../public' => public_path('vendor/nitm-api'),
            // ], 'nitm-api-assets');

            $this->publishes([
                __DIR__ . '/../publishes/config/nitm-api.php' => config_path('nitm-api.php'),
            ], 'nitm-api-config');
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

        app()->singleton('nitm.api', function () {
            return Rest::instance();
        });

        // Register clearlogs console command
        // TODO: This functionality is not finished yet
        $this->registerConsoleCommand('nitm.api-clearlogs', 'Nitm\Api\Console\ClearLogs');
    }

    /**
     * The boot() method is called right before a request is routed.
     */
    public function boot()
    {
        if (app()->environment() != 'testing') {

            // Checks and seeds settings for mismatches
            RestfulConfig::seedSettings();

            // Used for measuring script time passed
            ini_set('precision', 8);

            // Add event handler hooks for models
            Trivet::instance()->handleEvents();
        }
        if (app()->environment() != 'production') {
            trace_sql();
        }
    }
}
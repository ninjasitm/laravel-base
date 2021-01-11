<?php

namespace Nitm\Content;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Nitm\Content\Console\Commands\SyncSequences;

class NitmContentServiceProvider extends ServiceProvider
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
            'nitm-content'
        );

        $this->registerCommands();
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
            'domain' => config('nitm-content.domain', null),
            'namespace' => config('nitm-content.route-namespace'),
            'prefix' => config('nitm-content.route-prefix'),
            'middleware' => config('nitm-content.route-middleware'),
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
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                SyncSequences::class,
                ]
            );
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
            $this->publishes(
                [
                __DIR__ . '/Database/migrations' => database_path('migrations'),
                ], 'nitm-content-migrations'
            );

            // $this->publishes([
            //     __DIR__ . '/../public' => public_path('vendor/nitm-content'),
            // ], 'nitm-content-assets');

            $this->publishes(
                [
                __DIR__ . '/../publishes/config/nitm-content.php' => config_path('nitm-content.php'),
                ], 'nitm-content-config'
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
            __DIR__ . '/../config/nitm-content.php',
            'nitm-content'
        );
    }
}

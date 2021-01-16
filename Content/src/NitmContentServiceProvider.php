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
     * Define the NitmContent route model bindings.
     *
     * @return void
     */
    protected function defineRouteBindings()
    {
        Route::model('team', NitmContent::teamModel());

        Route::model('team_member', NitmContent::userModel());
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
     * Register the Spark services.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->registerAuthyService();

        $this->registerInterventionService();

        $this->registerApiTokenRepository();

        $services = [
            'Contracts\Repositories\AnnouncementRepository' => 'Repositories\AnnouncementRepository',
            'Contracts\Repositories\NotificationRepository' => 'Repositories\NotificationRepository',
            'Contracts\Repositories\TeamRepository' => 'Repositories\TeamRepository',
            'Contracts\Repositories\UserRepository' => 'Repositories\UserRepository',
            'Contracts\Repositories\LocalInvoiceRepository' => 'Repositories\StripeLocalInvoiceRepository',
            'Contracts\Repositories\PerformanceIndicatorsRepository' => 'Repositories\PerformanceIndicatorsRepository',
            'Contracts\Repositories\Geography\StateRepository' => 'Repositories\Geography\StateRepository',
            'Contracts\Repositories\Geography\CountryRepository' => 'Repositories\Geography\CountryRepository',
            'Contracts\InitialFrontendState' => 'InitialFrontendState',
            'Contracts\Interactions\Support\SendSupportEmail' => 'Interactions\Support\SendSupportEmail',
            'Contracts\Interactions\Subscribe' => 'Interactions\SubscribeUsingStripe',
            'Contracts\Interactions\SubscribeTeam' => 'Interactions\SubscribeTeamUsingStripe',
            'Contracts\Interactions\CheckPlanEligibility' => 'Interactions\CheckPlanEligibility',
            'Contracts\Interactions\CheckTeamPlanEligibility' => 'Interactions\CheckTeamPlanEligibility',
            'Contracts\Interactions\Auth\CreateUser' => 'Interactions\Auth\CreateUser',
            'Contracts\Interactions\Auth\Register' => 'Interactions\Auth\Register',
            'Contracts\Interactions\Settings\Profile\UpdateProfilePhoto' => 'Interactions\Settings\Profile\UpdateProfilePhoto',
            'Contracts\Interactions\Settings\Profile\UpdateContactInformation' => 'Interactions\Settings\Profile\UpdateContactInformation',
            'Contracts\Interactions\Settings\Teams\CreateTeam' => 'Interactions\Settings\Teams\CreateTeam',
            'Contracts\Interactions\Settings\Teams\AddTeamMember' => 'Interactions\Settings\Teams\AddTeamMember',
            'Contracts\Interactions\Settings\Teams\UpdateTeamMember' => 'Interactions\Settings\Teams\UpdateTeamMember',
            'Contracts\Interactions\Settings\Teams\UpdateTeamPhoto' => 'Interactions\Settings\Teams\UpdateTeamPhoto',
            'Contracts\Interactions\Settings\Teams\SendInvitation' => 'Interactions\Settings\Teams\SendInvitation',
            'Contracts\Interactions\Settings\Security\EnableTwoFactorAuth' => 'Interactions\Settings\Security\EnableTwoFactorAuthUsingAuthy',
            'Contracts\Interactions\Settings\Security\VerifyTwoFactorAuthToken' => 'Interactions\Settings\Security\VerifyTwoFactorAuthTokenUsingAuthy',
            'Contracts\Interactions\Settings\Security\DisableTwoFactorAuth' => 'Interactions\Settings\Security\DisableTwoFactorAuthUsingAuthy',
            'Contracts\Interactions\Settings\PaymentMethod\UpdatePaymentMethod' => 'Interactions\Settings\PaymentMethod\UpdateStripePaymentMethod',
            'Contracts\Interactions\Settings\PaymentMethod\RedeemCoupon' => 'Interactions\Settings\PaymentMethod\RedeemStripeCoupon',
        ];

        foreach ($services as $key => $value) {
            $this->app->singleton('Nitm\Content\\'.$key, 'Nitm\Content\\'.$value);
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

        if (! class_exists('NitmContent')) {
            class_alias('Nitm\Content\NitmContent', 'NitmContent');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../config/nitm-content.php',
            'nitm-content'
        );

        $this->registerServices();
    }
}
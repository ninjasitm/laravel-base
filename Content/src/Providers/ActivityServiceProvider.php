<?php

namespace Nitm\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Nitm\Content\Models\Activity;

/**
 * This provider allows the application to record an activitiy stream
 * {
 *    actor: {
 *       id: actor.username,
 *       objectType: Actor Type [user, blog, event,...],
 *       displayName: @param string,
 *       image: {}
 *    },
 *    title: @param string,
 *    verb: One of [
 *       like, follow, buy,...
 *    ],
 *    object: {
 *       id: object.publicId,
 *       url: @param string the Url to access the object,
 *       objectType: Object Type [user, blog, event,...],
 *       image: {}
 *    }
 *    target: {
 *       id: target.publicId,
 *       url: @param string the Url to access the target,
 *       objectType: target Type [user, blog, event,...],
 *       image: {}
 *    }
 *    created_at: Timestamp this activity was recorded
 * }.
 */
class ActivityServiceProvider extends ServiceProvider
{
    protected function supportedActivities()
    {
        return \Config::get('app.observers') ?: [];
    }
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        foreach ($this->supportedActivities() as $class => $observerClass) {
            $class::observe($observerClass);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('Nitm\ActivityProvider', function ($observer) {
            return new Activity();
        });
    }
}
<?php

return [
    /**
     * User model
     */
    'user_model' => env('NITM_CONTENT_USER_MODEL', 'App\User'),
    /**
     * User model
     */
    'team_model' => env('NITM_CONTENT_TEAM_MODEL', 'App\Team'),
    /**
     * The domain for the app
     */
    'domain' => null,
    /**
     * The route prefis
     */
    'route-prefix' => 'api',
    /**
     * The extra route middleware to use
     */
    'route-midleware' => [],
    /**
     * The controller namespace for the routes
     */
    'route-namespace' => 'Nitm\Content\Http\Controllers'
];
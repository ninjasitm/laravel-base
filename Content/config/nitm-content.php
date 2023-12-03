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
     * User profile model
     */
    'user_profile_model' => env('NITM_CONTENT_USER_PROFILE_MODEL', 'App\UserProfile'),
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
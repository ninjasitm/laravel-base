<?php

return [
    /**
     * User model
     */
    'user_model' => env('NITM_CONTENT_USER_MODEL', 'App\User'),
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
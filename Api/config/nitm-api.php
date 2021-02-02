<?php

return [
    /**
     * User model
     */
    'user_model' => env('NITM_API_USER_MODEL', 'App\User'),

    /**
     * Team model
     */
    'team_model' => env('NITM_API_TEAM_MODEL', 'App\Team'),

    /**
     * Enable to disable social auth
     */
    'enable_social_auth' => env('NITM_API_SOCIAL_AUTH_ENABLE', false),

    /**
     * The prefix for social auth routes
     */
    'social_auth_prefix' => 'auth',

    /**
     * The middleware to use for social auth
     */
    'social_auth_middleware' => [],

    /**
     * THe namespace for social auth controllers
     */
    'social_auth_namesapce' => 'Nitm\\Api\\Http\\Controllers'
];
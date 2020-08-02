<?php

if (!\Config::get('app.apiEnabled')) {
    return;
}
$baseUrl = \Config::get('app.apiVersion');
$devurl = '';

/*
 * Authentication routes
 *
 */

Route::group(['prefix' => $baseUrl.'/auth', 'middleware' => 'cors'], function () use ($baseUrl) {
    foreach (['login', 'logout', 'register', 'activate', 'ping', 'reset', 'recover'] as $action) {
        if (App::runningInConsole()) {
            Route::post('/'.$action, 'Nitm\Api\Controllers\AuthController@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
        } else {
            Route::post('/'.$action, 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::options('/'.$action.'/{id}', 'Nitm\Api\Components\Auth@onRun')->name($baseUrl);
        }
    }
    foreach (['restore', 'update', 'profile'] as $action) {
        if (App::runningInConsole()) {
            Route::post('/'.$action.'/{id?}', 'Nitm\Api\Controllers\AuthController@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
        } else {
            Route::post('/'.$action.'/{id?}', 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::put('/'.$action.'/{id?}', 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::patch('/'.$action.'/{id?}', 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::options('/'.$action.'/{id}', 'Nitm\Api\Components\Auth@onRun')->name($baseUrl);
        }
    }
    foreach (['profile'] as $action) {
        if (App::runningInConsole()) {
            Route::get('/'.$action.'/{id?}', 'Nitm\Api\Controllers\AuthController@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
        } else {
            Route::get('/'.$action.'/{id?}', 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::options('/'.$action.'/{id}', 'Nitm\Api\Components\Auth@onRun')->name($baseUrl);
        }
    }
    foreach (['deactivate', 'delete'] as $action) {
        if (App::runningInConsole()) {
            Route::delete('/'.$action.'/{id}', 'Nitm\Api\Controllers\AuthController@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
        } else {
            Route::delete('/'.$action.'/{id}', 'Nitm\Api\Components\Auth@'.$action.'Account')->name($baseUrl.'/auth/'.$action);
            Route::options('/'.$action.'/{id}', 'Nitm\Api\Components\Auth@onRun')->name($baseUrl);
        }
    }
});

/*
 * Social account connection routes
 */
Route::group(['prefix' => $baseUrl.'/connect', 'middleware' => 'cors'], function () use ($baseUrl) {
    if (App::runningInConsole()) {
        Route::post('/{network}', 'Nitm\Api\Controllers\SocialAccountController@connectAccount')->name($baseUrl.'/connect');
        Route::get('/{network}', 'Nitm\Api\Controllers\SocialAccountController@connectAccount')->name($baseUrl.'/connect');
    } else {
        Route::post('/{network}', 'Nitm\Api\Components\SocialAccount@connectAccount')->name($baseUrl.'/connect');
        Route::get('/{network}', 'Nitm\Api\Components\SocialAccount@connectAccount')->name($baseUrl.'/connect');
        Route::options('/{network}', 'Nitm\Api\Components\SocialAccount@onRun')->name($baseUrl);
    }
});

/*
 * Routes for API API
 * These are the create and update routes
 **/
Route::group(['prefix' => $baseUrl, 'middleware' => 'cors'], function () use ($baseUrl) {
    if (App::runningInConsole()) {
        Route::post('/{req}', 'Nitm\Api\Controllers\ApiController@create')->name($baseUrl);
        Route::post('/{req}/{id}', 'Nitm\Api\Controllers\ApiController@update')->name($baseUrl);
        Route::put('/{req}/{id}', 'Nitm\Api\Controllers\ApiController@update')->name($baseUrl);
    } else {
        Route::post('/{req}', 'Nitm\Api\Components\Api@create')->name($baseUrl);
        Route::post('/{req}/{id}', 'Nitm\Api\Components\Api@update')->name($baseUrl);
        Route::put('/{req}/{id}', 'Nitm\Api\Components\Api@update')->name($baseUrl);
        Route::options('/{req}/{id}', 'Nitm\Api\Components\Api@onRun')->name($baseUrl);
    }
});

/*
 * Routes for API API
 * These are the delete routes
 **/
Route::group(['prefix' => $baseUrl, 'middleware' => 'cors'], function () use ($baseUrl) {
    if (App::runningInConsole()) {
        Route::delete('/{req}/{id}', 'Nitm\Api\Controllers\ApiController@delete')->name($baseUrl);
    } else {
        Route::delete('/{req}/{id}', 'Nitm\Api\Components\Api@delete')->name($baseUrl);
        Route::options('/{req}/{id}', 'Nitm\Api\Components\Api@onRun')->name($baseUrl);
    }
});

/*
 * Routes for API API
 * These are the get routes for getting a single and multiple records
 **/
Route::group(['prefix' => $baseUrl, 'middleware' => 'cors'], function () use ($baseUrl) {
    if (App::runningInConsole()) {
        Route::get('/{req}', 'Nitm\Api\Controllers\ApiController@readAll')->name($baseUrl);
        Route::get('/{req}/{id}', 'Nitm\Api\Controllers\ApiController@read')->name($baseUrl);
    } else {
        Route::get('/{req}', 'Nitm\Api\Components\Api@readAll')->name($baseUrl);
        Route::get('/{req}/{id?}', 'Nitm\Api\Components\Api@read')->name($baseUrl);
        Route::options('/{req}/{id?}', 'Nitm\Api\Components\Api@onRun')->name($baseUrl);
    }
});

/*
 * Routes for API API page configuration
 **/
Route::group(['prefix' => $baseUrl.'/config', 'middleware' => 'cors'], function () use ($baseUrl) {
    if (App::runningInConsole()) {
        Route::get('/{req}', 'Nitm\Api\Controllers\ApiController@readAll')->name($baseUrl);
    } else {
        Route::get('/{req}', 'Nitm\Api\Components\Api@readAll')->name($baseUrl);
        Route::options('/{req}', 'Nitm\Api\Components\Api@onRun')->name($baseUrl);
    }
});

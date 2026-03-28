<?php

use Illuminate\Support\Facades\Route;
use Nitm\Api\Http\Controllers\Auth\SocialAuthController;

if (config('nitm-api.enable_social_auth')) {
    Route::group(
        [
            'prefix'     => config('nitm-api.social_auth_prefix', 'auth'),
            'middleware' => config('nitm-api.social_auth_middleware', [])
        ], function () {
            // Connected Accounts
            Route::get('/connected-accounts', [SocialAuthController::class, 'getAccounts'])->name('auth.social.index');
            Route::get('/connected-accounts/{social}', [SocialAuthController::class, 'getAccountCustom'])->name('auth.social.show');
            Route::get('/connected-accounts/refresh/{social}', [SocialAuthController::class, 'refreshToken'])->name('auth.social.refresh');
            Route::post('/connected-accounts/{social}/callback', [SocialAuthController::class, 'callbackCustom'])->name('auth.social.callback');
            Route::post('/connected-accounts/{social}', [SocialAuthController::class, 'callforward'])->name('auth.social.callforward');
            Route::delete('/connected-accounts/{social}', [SocialAuthController::class, 'detachAccountCustom'])->name('auth.social.detach');
        }
    );
}
<?php
if(config('nitm-api.enable_social_auth')) {
    Route::group(
        [
        'prefix' => config('nitm-api.social_auth_prefix', 'auth'),
        'middleware' => config('nitm-api.social_auth_middleware', [])
        ], function () {
            // Connected Accounts
            Route::get('/connected-accounts', 'Auth\SocialAuthController@getAccounts')->name('auth.social.index');
            Route::get('/connected-accounts/{social}', 'Auth\SocialAuthController@getAccountCustom')->name('auth.social.show');
            Route::get('/connected-accounts/refresh/{social}', 'Auth\SocialAuthController@refreshToken')->name('auth.social.refresh');
            Route::post('/connected-accounts/{social}/callback', 'Auth\SocialAuthController@callbackCustom')->name('auth.social.callback');
            Route::post('/connected-accounts/{social}', 'Auth\SocialAuthController@callforward')->name('auth.social.callforward');
            Route::delete('/connected-accounts/{social}', 'Auth\SocialAuthController@detachAccountCustom')->name('auth.social.detach');
        }
    );
}
?>
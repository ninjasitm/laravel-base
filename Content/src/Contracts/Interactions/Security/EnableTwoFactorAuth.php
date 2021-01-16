<?php

namespace Nitm\Content\Contracts\Interactions\Security;

interface EnableTwoFactorAuth
{
    /**
     * Enable two-factor authentication for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $countryCode
     * @param  string                                     $phoneNumber
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function handle($user, $countryCode, $phoneNumber);
}

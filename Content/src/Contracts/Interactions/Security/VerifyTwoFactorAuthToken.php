<?php

namespace Nitm\Content\Contracts\Interactions\Security;

interface VerifyTwoFactorAuthToken
{
    /**
     * Verify a two-factor authentication token for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     * @return bool
     */
    public function handle($user, $token);
}

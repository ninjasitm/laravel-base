<?php
namespace Nitm\Api\Auth;

use MadWeb\SocialAuth\Events\SocialUserAttached;
use Laravel\Socialite\Contracts\User as SocialUser;
use MadWeb\SocialAuth\Contracts\SocialAuthenticatable;
use MadWeb\SocialAuth\SocialProviderManager as BaseSocialProviderManager;

/**
 * SocialProviderManager
 */
class SocialProviderManager extends BaseSocialProviderManager
{
    /**
     * @param  string $key
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function socialTeamQuery(string $key)
    {
        return $this->social->teams()->wherePivot(config('social-auth.foreign_keys.socials'), $key);
    }

    /**
     * Gets user by unique social identifier.
     *
     * @param  string $key
     * @return mixed
     */
    public function getTeamByKey(string $key)
    {
        return $this->socialTeamQuery($key)->first();
    }

    /**
     * @param SocialAuthenticatable $user
     * @param SocialUser            $socialUser
     */
    public function attachTeam($team, SocialUser $socialUser, $offlineToken = null)
    {
        $team->attachSocialCustom(
            $this->social,
            $socialUser->getId(),
            $socialUser->token,
            $offlineToken,
            $socialUser->expiresIn ?? request()->input('expires_in')
        );
    }

    /**
     * @param SocialAuthenticatable $user
     * @param SocialUser            $socialUser
     */
    public function attachUser(SocialAuthenticatable $user, SocialUser $socialUser, $offlineToken = null)
    {
        $user->attachSocialCustom(
            $this->social,
            $socialUser->getId(),
            $socialUser->token,
            $offlineToken,
            $socialUser->expiresIn ?? request()->input('expires_in')
        );

        event(new SocialUserAttached($user, $this->social, $socialUser));
    }
}
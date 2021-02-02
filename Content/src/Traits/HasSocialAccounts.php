<?php

trait HasSocialAccounts
{


    /**
     * Custom attachSocial for teams, taken from Mad Web Laravel Social Package
     * Attach social network provider to the user.
     *
     * @param SocialProvider $social
     * @param string         $socialId
     * @param string         $token
     * @param int            $expiresIn
     */
    public function attachSocialCustom($social, string $socialId, string $token, string $offlineToken = null, int $expiresIn = null)
    {
        $data = ['social_id' => $socialId, 'token' => $token, 'offline_token' => $offlineToken];

        $expiresIn = $expiresIn ?: 3600;
        $expiresIn = date_create('now')
            ->add(\DateInterval::createFromDateString($expiresIn . ' seconds'));

        $data['expires_in'] = $expiresIn;

        $this->socials()->attach($social, $data);
    }

    /**
     * User socials relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function socials()
    {
        return $this->belongsToMany(\Nitm\Content\Models\SocialProvider::class, $this->getSocialAccountsTable())
            ->as('token')
            ->withPivot('token', 'expires_in', 'offline_token');
    }
}
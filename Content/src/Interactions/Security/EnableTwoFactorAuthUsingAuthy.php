<?php

namespace Nitm\Content\Interactions\Security;

use Nitm\Content\Services\Security\Authy;
use Nitm\Content\Contracts\Interactions\Security\EnableTwoFactorAuth as Contract;

class EnableTwoFactorAuthUsingAuthy implements Contract
{
    /**
     * The Authy service instance.
     *
     * @var \Nitm\Content\Services\Security\Authy
     */
    protected $authy;

    /**
     * Create a new interaction instance.
     *
     * @param  \Nitm\Content\Services\Security\Authy $authy
     * @return void
     */
    public function __construct(Authy $authy)
    {
        $this->authy = $authy;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($user, $countryCode, $phoneNumber)
    {
        $user->forceFill(
            [
            'authy_id' => $this->authy->enable(
                $user->email, $phoneNumber, $countryCode
            ),
            ]
        )->save();

        return $user;
    }
}

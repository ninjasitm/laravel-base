<?php

namespace Nitm\Content\Interactions\Security;

use Nitm\Content\Services\Security\Authy;
use Nitm\Content\Contracts\Interactions\Security\DisableTwoFactorAuth as Contract;

class DisableTwoFactorAuthUsingAuthy implements Contract
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
    public function handle($user)
    {
        $this->authy->disable($user->authy_id);

        $user->forceFill(
            [
            'authy_id' => null,
            ]
        )->save();

        return $user;
    }
}

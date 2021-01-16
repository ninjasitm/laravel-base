<?php

namespace Nitm\Content\Contracts\Interactions\Teams;

interface SendInvitation
{
    /**
     * Create and mail an invitation to the given e-mail address.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  string                    $email
     * @param  string                    $role
     * @return \Nitm\Content\Models\Invitation
     */
    public function handle($team, $email, $role);
}
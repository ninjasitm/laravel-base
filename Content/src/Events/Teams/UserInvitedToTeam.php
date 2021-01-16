<?php

namespace Nitm\Content\Events\Teams;

class UserInvitedToTeam
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * The user instance.
     *
     * @var mixed
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  mixed                     $user
     * @return void
     */
    public function __construct($team, $user)
    {
        $this->team = $team;
        $this->user = $user;
    }
}

<?php

namespace Nitm\Content\Events\Teams;

class GuestInvitedToTeam
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * The invitation instance.
     *
     * @var mixed
     */
    public $invitation;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  mixed                     $invitation
     * @return void
     */
    public function __construct($team, $invitation)
    {
        $this->team = $team;
        $this->invitation = $invitation;
    }
}

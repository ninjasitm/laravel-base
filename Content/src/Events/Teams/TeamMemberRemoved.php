<?php

namespace Nitm\Content\Events\Teams;

class TeamMemberRemoved
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * The team member instance.
     *
     * @var mixed
     */
    public $member;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  mixed                     $member
     * @return void
     */
    public function __construct($team, $member)
    {
        $this->team = $team;
        $this->member = $member;
    }
}

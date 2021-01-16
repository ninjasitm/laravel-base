<?php

namespace Nitm\Content\Events\Teams;

class TeamOwnerAdded
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * The team owner instance.
     *
     * @var mixed
     */
    public $owner;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  mixed                     $owner
     * @return void
     */
    public function __construct($team, $owner)
    {
        $this->team = $team;
        $this->owner = $owner;
    }
}

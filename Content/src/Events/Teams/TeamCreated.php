<?php

namespace Nitm\Content\Events\Teams;

class TeamCreated
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @return void
     */
    public function __construct($team)
    {
        $this->team = $team;
    }
}

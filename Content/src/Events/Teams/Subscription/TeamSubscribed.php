<?php

namespace Nitm\Content\Events\Teams\Subscription;

class TeamSubscribed
{
    /**
     * The team instance.
     *
     * @var \Nitm\Content\Models\Team
     */
    public $team;

    /**
     * The plan the team subscribed to.
     *
     * @var \Nitm\Content\Plan
     */
    public $plan;

    /**
     * Create a new event instance.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  \Nitm\Content\Plan        $plan
     * @return void
     */
    public function __construct($team, $plan)
    {
        $this->team = $team;
        $this->plan = $plan;
    }
}

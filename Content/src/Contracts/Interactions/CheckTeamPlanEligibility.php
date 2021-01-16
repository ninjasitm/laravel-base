<?php

namespace Nitm\Content\Contracts\Interactions;

interface CheckTeamPlanEligibility
{
    /**
     * Determine if the team is eligible to switch to the given plan.
     *
     * @param  \Nitm\Content\Team $team
     * @param  \Nitm\Content\Plan $plan
     * @return bool
     */
    public function handle($team, $plan);
}

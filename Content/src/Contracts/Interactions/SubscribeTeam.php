<?php

namespace Nitm\Content\Contracts\Interactions;

interface SubscribeTeam
{
    /**
     * Subscribe the team to a subscription plan.
     *
     * @param  \Nitm\Content\Team $team
     * @param  \Nitm\Content\Plan $plan
     * @param  bool               $fromRegistration
     * @param  array              $data
     * @return \Nitm\Content\Team
     */
    public function handle($team, $plan, $fromRegistration, array $data);
}

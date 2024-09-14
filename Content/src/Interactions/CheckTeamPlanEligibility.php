<?php

namespace Nitm\Content\Interactions;

use Nitm\Content\Contracts\Interactions\CheckTeamPlanEligibility as Contract;

class CheckTeamPlanEligibility implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($team, $plan)
    {
        return true;
    }
}

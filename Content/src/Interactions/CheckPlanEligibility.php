<?php

namespace Nitm\Content\Interactions;

use Nitm\Content\Contracts\Interactions\CheckPlanEligibility as Contract;

class CheckPlanEligibility implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($user, $plan)
    {
        return true;
    }
}

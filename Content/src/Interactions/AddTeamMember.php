<?php

namespace Nitm\Content\Interactions;

use Nitm\Content\NitmContent;
use Nitm\Content\Events\Teams\TeamMemberAdded;
use Nitm\Content\Contracts\Interactions\AddTeamMember as Contract;

class AddTeamMember implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($team, $user, $role = null)
    {
        $team->users()->attach($user, ['role' => $role ?: NitmContent::defaultRole()]);

        event(new TeamMemberAdded($team, $user));

        try {
            if (NitmContent::chargesTeamsPerMember() && $team->subscription()
                && $team->users()->count() > 1
            ) {
                $team->addSeat();
            }
        } catch (Exception $e) {
            // We'll do nothing since members are added by accepting an invitation so
            // there's no immediate action the invited user can take. We'll leave it
            // to the team owner to notice the subscription has a pending payment.
        }

        return $team;
    }
}
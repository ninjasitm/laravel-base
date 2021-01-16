<?php

namespace Nitm\Content\Contracts\Interactions\Teams;

interface AddTeamMember
{
    /**
     * Add a user to the given team.
     *
     * @param  \Nitm\Content\Models\Team                  $team
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string|null                                $role
     * @return \Nitm\Content\Models\Team
     */
    public function handle($team, $user, $role = null);
}
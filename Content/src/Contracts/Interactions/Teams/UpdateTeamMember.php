<?php

namespace Nitm\Content\Contracts\Interactions\Teams;

interface UpdateTeamMember
{
    /**
     * Get a validator instance for the given data.
     *
     * @param \Nitm\Content\Models\Team                  $team
     * @param \Illuminate\Contracts\Auth\Authenticatable $member
     * @param iterable                                     $data
     * @return \Illuminate\Validation\Validator
     */
    public function validator($team, $member, array $data);

    /**
     * Update the given team member.
     *
     * @param \Nitm\Content\Models\Team                  $team
     * @param \Illuminate\Contracts\Auth\Authenticatable $member
     * @param iterable                                     $data
     * @return void
     */
    public function handle($team, $member, array $data);
}

<?php

namespace Nitm\Content\Contracts\Interactions\Teams;

interface UpdateTeamPhoto
{
    /**
     * Get a validator instance for the given data.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  array                     $data
     * @return \Illuminate\Validation\Validator
     */
    public function validator($team, array $data);

    /**
     * Update the team's photo.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  array                     $data
     * @return \Nitm\Content\Models\Team
     */
    public function handle($team, array $data);
}
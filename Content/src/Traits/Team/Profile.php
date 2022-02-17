<?php

namespace Nitm\Content\Traits\Team;

use Nitm\Content\Models\TeamProfile;

trait Profile
{
    public function profile()
    {
        return $this->hasOne(TeamProfile::class, 'team_id');
    }
}
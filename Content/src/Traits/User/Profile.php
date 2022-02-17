<?php

namespace Nitm\Content\Traits\User;

use Nitm\Content\Models\Profile as ProfileModel;

trait Profile
{
    public function profile()
    {
        return $this->hasOne(ProfileModel::class);
    }
}
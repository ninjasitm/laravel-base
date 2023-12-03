<?php

namespace Nitm\Content\Traits\User;

use Illuminate\Support\Arr;
use Nitm\Content\Models\Profile as ProfileModel;

trait Profile
{
    /**
     * Get the user's profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        $class = config('nitm-content.user_profile_model', ProfileModel::class);
        return $this->hasOne($class, 'user_id');
    }

    /**
     * Get the user's profile photo URL attribute.
     *
     * @return string
     */
    public function getProfilePhotoPathAttribute(): string
    {
        return Arr::get($this->attributes, 'profile_photo_path') ?: "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email)));
    }
}

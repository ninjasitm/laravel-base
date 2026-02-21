<?php

namespace Nitm\Content\Traits;

use Nitm\Content\Models\BaseContent;
use Nitm\Content\Models\Follow;

/**
 * Traits for Nitm Content MOdel.
 */
trait User
{

    public function getMorphClass()
    {
        return 'Nitm\Content\Models\User';
    }

    public static function apiFind($id, $options = [])
    {
        unset($options['hashedId']);

        return static::internalApiFind($id, array_merge([
            'for' => 'single',
            'columns' => '*',
            'stringColumns' => ['username', 'email'],
        ], $options));
    }

    /**
    * Custom API query function.
    *
    * @param array   $options  Array of parameters for the query builder
    * @param bool    $multiple Is this a request for multiple records?
    * @param Builder $query    The query to use
    *
    * @return [type] [description]
    */
    public static function apiQuery($options = [], $multiple = false, $query = null)
    {
        extract($options);
        $query = $query ?: static::query();
        if (!isset($forFeed)) {
            $with = isset($with) ? $with : [];
        } else {
            $with = $query->getModel()->eagerWith;
        }
        static::addWithToQuery($query, $with);
        return $query;
    }

    public function generateApiToken()
    {
        if (!$this->apiToken) {
            $token = $this->apiToken()->create([
        'user_id' => $this->id,
        ]);
            $this->setRelation('apiToken', $token);
        }
    }

    public function getAuthTokenAttribute()
    {
        if ($this->apiToken) {
            return $this->apiToken->token;
        }

        return 'public';
    }

    public function checkToken()
    {
        if (!$this->apiToken) {
            $this->generateApiToken();
        } elseif ($this->apiToken->isExpired) {
            $this->apiToken->renew();
        } else {
            $this->apiToken->updateSignature();
        }
    }

    /**
    * Necessary to limit relations when loading a User based relation, otherwise all relations load.
    *
    * @param [type] $query [description]
    *
    * @return [type] [description]
    */
    public function scopeLimitRelations($query)
    {
        $eagerLoads = $query->getEagerLoads();

        return $query->setEagerLoads(array_intersect_key($eagerLoads, [
        'avatar' => [],
        ]));
    }

    public function getLoginAttribute()
    {
        return $this->username;
    }

    public function getDisplayNameAttribute()
    {
        return strlen($this->name) ? $this->name : $this->username;
    }

    public function changePassword($password, $newPassword, $newPasswordConfirmation)
    {
        if ($password === $newPassword || $password === $newPasswordConfirmation) {
            Flash::error("You can't use the same password!");
        } elseif ($this->checkPassword($password)) {
            $validator = \Validator::make([
                'password' => $newPassword,
                'password_confirmation' => $newPasswordConfirmation
            ], [
                'password' => 'required|between:2,32|confirmed',
                'password_confirmation' => 'required_with:password|between:2,32'
            ]);
            if ($validator->fails()) {
                throw new \ValidationException($validator);
            } else {
                $this->password = $newPassword;
                $this->password_confirmation = $newPasswordConfirmation;
                $this->save();
            }
            return true;
        } else {
            throw new \ValidationException([
                'verify_password' => "Invalid password!"
            ]);
        }
    }

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->surname;
    }

    public function setFullNameAttribute($name)
    {
        $name = explode(' ', $name);
        $this->name = array_shift($name);
        $this->surname = implode(' ', (array)$name);
    }

    public function getShortNameAttribute()
    {
        $name = explode(' ', $this->name);
        return $name[0];
    }

    public function getFirstNameAttribute()
    {
        $name = explode(' ', $this->name);
        return $name[0];
    }

    public function getLastNameAttribute()
    {
        $name = explode(' ', $this->name);
        return count($name) > 1 ? array_pop($name) : '';
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? $this->avatar->getPath() : '/storage/app/media/images/logos/avatar.png';
    }


    public function isFollowing($id)
    {
        return Follow::where([
            'follower_id' => $this->id,
            'followee_id' => $id
        ])->count() == 1;
    }

    public function isCurrentUser($id)
    {
        return $id == $this->id;
    }
}

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
        return 'RainLab\User\Models\User';
    }

    public function setProfileAttribute($profile)
    {
        if (!empty($profile)) {
            foreach ((array) $profile as $key => $value) {
                $key = 'iu_'.$key;
                switch ($key) {
                    case 'iu_bio':
                        $key = 'iu_about';
                        break;
                    case 'iu_social':
                        $this->setProfileAttribute($value);
                        break;
                    case 'iu_location':
                        $this->setProfileAttribute($value);
                        break;
                }
                if (in_array($key, $this->fillable)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function getProfileAttribute()
    {
        return [
            'bio' => $this->iu_about,
            'bio_short' => $this->iu_about && strlen($this->iu_about) > 240 ? preg_replace('/\s+?(\S+)?$/', '', substr($this->iu_about, 0, 240)).' ...' : '',
            'website' => $this->iu_webpage,
            'company' => $this->iu_company,
            'social' => [
                'twitter' => $this->iu_twitter,
                'facebook' => $this->iu_facebook,
                'linkedin' => $this->iu_blog,
            ],
            'location' => [
                'street_addr' => $this->street_addr,
                'city' => $this->city,
                'zip' => $this->zip,
                'state' => $this->state ? $this->state->toArray() : [],
                'country' => $this->country ? $this->country->toArray() : [],
            ],
        ];
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

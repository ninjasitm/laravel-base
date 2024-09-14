<?php

namespace Nitm\Content\Models;

use Model;
use DB;
use Validator;
use Carbon\Carbon;

/**
 * Model.
 */
class Follow extends BaseAction
{
    /*
      * Validation
      */
    public $rules = [
        'follower_id' => 'exists:users,id,deleted_at,NULL|unique_follow:nitm_follows,follower_id,followee_id',
        'followee_id' => 'exists:users,id,deleted_at,NULL|no_self_follow',
    ];

    public $customMessages = [
        'exists' => "The user you want to follow doesn't exist",
        'unique_follow' => 'Already following!',
        'no_self_follow' => 'No self follows!',
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_follows';

    public $casts = [
        'followee' => 'array',
        'follower' => 'array',
    ];

    public $fillable = [
        'followee', 'follower', 'title', 'type', 'start_date', 'end_date', 'follower_id', 'followee_id', 'is_admin_action', 'deleted_at',
    ];

    public $visible = [
        'id', 'followee', 'follower', 'title', 'type', 'start_date', 'end_date', 'count',
    ];

    public $with = ['follower', 'followee'];

    public $hasOne = [
        'follower' => [
            'Nitm\Content\Models\SimpleUser',
            'key' => 'id',
            'otherKey' => 'follower_id',
        ],
        'followee' => [
            'Nitm\Content\Models\SimpleUser',
            'key' => 'id',
            'otherKey' => 'followee_id',
        ],
    ];

    protected $dates = ['deleted_at', 'start_date'];

    protected static function setupCustomValidators()
    {
        Validator::extend('no_self_follow', function ($attribute, $value, $parameters, $validator) {
            $currentUser = static::getCurrentUser();

            if (!$currentUser) {
                return false;
            }

            return $validator->getData()['followee_id'] != $currentUser->id;
        });

        Validator::extend('unique_follow', function ($attribute, $value, $parameters, $validator) {
            // Get table name from first parameter
            $table = array_shift($parameters);

            // Build the query
            $query = DB::table($table);
            $query->select('id');

            // if (count(array_filter($validator->getData())) != count($parameters)) {
            //  return false;
            // }

            // Add the field conditions
            $followeeId = array_get($validator->getData(), 'followee_id') ?: (int) trim(array_get($validator->getData(), 'followee._model.id')) ?: (int) trim(array_get($validator->getData(), 'followee'));
            $query->where([
                'followee_id' => $followeeId,
                'follower_id' => static::getCurrentUser()->id,
                'deleted_at' => null,
            ]);

            // Validation result will be false if any rows match the combination
            return $query->count() == 0;
        });
    }

    public static function recordFollow(FollowObserver $observer)
    {
        $now = Carbon::now();
        $actionName = $observer->getActionName();
        $startDate = $actionName == 'follow' ? $now : null;
        $endDate = $actionName != 'follow' ? $now : null;
        $model = static::make([
            'type' => $actionName,
            'follower' => $observer->getFollower(),
            'followee' => $observer->getFollowee(),
            'follower_id' => $observer->getFollower()['_model']['id'],
            'followee_id' => $observer->getFollowee()['_model']['id'],
            'title' => $observer->getTitleString(),
            'is_admin_action' => $observer->getIsAdminAction(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        $model->save();
        $model->isRecorded = true;
        $observer->finish();
    }

    //  public function attributesToArray()
    //  {
    //      $attributes = parent::attributesToArray();
    //      unset($attributes['follower']['_model'], $attributes['followee']['_model']);

    //      return $attributes;
    //  }

    public function getFollowerNameAttribute()
    {
        $name = array_get($this->follower, 'displayName');

        return $name ?: array_get($this->follower, 'name');
    }

    public function getFollowerDisplayNameAttribute()
    {
        return $this->follower['displayName'];
    }

    public function getFollowerTypeAttribute()
    {
        return $this->follower['type'];
    }

    /**
     * This function was previously named getFollowerIdAttribute which caused problems for eager loading relations.
     *
     * @method getFollowerPublicIdAttribute
     *
     * @return [type] [description]
     */
    public function getFollowerPublicIdAttribute()
    {
        return $this->follower['id'];
    }

    public function getFolloweeNameAttribute()
    {
        return array_get($this->followee, 'displayName', array_get($this->followee, 'name'));
    }

    public function getFolloweeDisplayNameAttribute()
    {
        return $this->followee['displayName'];
    }

    public function getFolloweeTypeAttribute()
    {
        return $this->followee['type'];
    }

    /**
     * This function was previously named getFolloweeIdAttribute which caused problems for eager loading relations.
     *
     * @method getFolloweePublicIdAttribute
     *
     * @return [type] [description]
     */
    public function getFolloweePublicIdAttribute()
    {
        return $this->followee['id'];
    }

    public function beforeSave()
    {
        if (is_object($this->followee)) {
            $user = $this->followee;
        } else {
            $user = User::apiFind($this->followee);
        }
        $attributes = [];
        $existing = self::where([
            'follower_id' => (int) $this->getCurrentUser()->id,
            'followee_id' => $user ? (int) $user->id : null,
            'type' => 'unfollow',
        ])->withTrashed()->first();
        if ($existing) {
            $attributes = [
                'type' => 'follow',
                'deleted_at' => null,
                'end_date' => null,
            ];
            $this->id = $existing->id;
            $this->exists = true;
        } else {
            $attributes = [
                'follower_id' => (int) $this->getCurrentUser()->id,
                'followee_id' => $user ? (int) $user->id : null,
                'type' => 'follow',
                'end_date' => null,
            ];
        }
        $attributes['start_date'] = \Carbon\Carbon::now();
        $this->fill($attributes);
        $this->rules['followee_id'] = 'required|' . $this->rules['followee_id'];
        $this->rules['follower_id'] = 'required|' . $this->rules['follower_id'];

        return $this->validate();
    }

    public function beforeDelete()
    {
        $attributes = [];
        $attributes['type'] = 'unfollow';
        $attributes['end_date'] = \Carbon\Carbon::now();
        $attributes['start_date'] = null;
        $attributes['end_date'] = Carbon::now();
        $this->fill($attributes);
    }

    /**
     * Custom Find a model by custom keys.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public static function apiFind($id, $options = ['*'])
    {
        extract($options);
        $columns = isset($columns) ? $columns : ['*'];
        if (is_numeric($id)) {
            return parent::apiFind($id, $options);
        } else {
            return static::whereIn('follower_id', function ($query) use ($id) {
                $query->select('id')->from((new User())->getTable())->where('username', '=', $id);
            })->select($columns)->first();
        }
    }

    /**
     * Custom Find a model by custom keys.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public static function apiQuery($options = [], $multiple = false, $query = null)
    {
        extract($options);
        $columns = isset($columns) ? $columns : ['*'];
        $id = \Auth::getUser() ? \Auth::getUser()->id : 0;
        if (is_numeric($id)) {
            return static::where('follower_id', '=', $id)->select($columns);
        } else {
            return static::whereIn('follower_id', function ($query) use ($id) {
                $query->select('id')->from((new User())->getTable())->where('username', '=', $id);
            })->select($columns);
        }
    }

    public function scopeUserFollowerLimit($query)
    {
        return $query->take(10);
    }

    public function scopeFollowLimit($query)
    {
        return $query->take(10);
    }
}

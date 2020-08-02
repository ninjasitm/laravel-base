<?php

namespace Nitm\Content\Models;

use Model;
use Validator;

/**
 * Model.
 */
class Favorite extends BaseAction
{
    /*
      * Validation
      */
    public $rules = [
        'user_id' => 'no_self_favorite',
        'thing_id' => 'thing_exists|unique_favorite:nitm_favorites',
        'thing' => 'thing_exists|unique_favorite:nitm_favorites',
    ];

    public $customMessages = [
        'thing_exists' => "The content you want to favorite doesn't exist",
        'unique_favorite' => 'Already favorited!',
        'thing_id.unique' => 'Already favorited!',
        'no_self_favorite' => 'No self favoriting!',
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_favorites';

    public $visible = ['user', 'thing', 'id', 'count'];

    public $fillable = ['thing', 'thing_id', 'thing_type', 'user_id', 'deleted_at'];

    public $with = ['user'];

    public $belongsTo = [
        'user' => [
            'Nitm\Content\Models\RelatedUser',
            'key' => 'user_id',
            'otherKey' => 'id',
        ],
    ];

    public $dynamicContentConfig = [
        'key' => 'thing_id',
        'typeKey' => 'thing_type',
    ];

    protected static function setupCustomValidators()
    {
        Validator::extend('no_self_favorite', function ($attribute, $value, $parameters, $validator) {
            $data = array_map(function ($value) {
                return preg_replace('/[^\da-z]/i', '', $value);
            }, $validator->getData());

            $currentUser = static::getCurrentUser();

            if (!$currentUser) {
                return false;
            }

            return ($data['thing_type'] != 'user') || (($data['thing_type'] == 'user') && ($data['user_id'] == $currentUser->id));
        });

        Validator::extend('unique_favorite', function ($attribute, $value, $parameters, $validator) {
            $thingId = array_get($validator->getData(), 'thing_id') ?: (int) trim(array_get($validator->getData(), 'object._model.id')) ?: (int) trim(array_get($validator->getData(), 'object'));
            $thingType = array_get($validator->getData(), 'thing_type', array_get($validator->getData(), 'type'));

            return static::findFavorite($thingId, $thingType, true)->count() == 0;
        });

        Validator::extend('thing_exists', function ($attribute, $value, $parameters, $validator) {
            // Get table name from first parameter
            return static::findThingId(array_get($validator->getData(), 'thing_id', array_get($validator->getData(), 'thing')), array_get($validator->getData(), 'thing_type', array_get($validator->getData(), 'type'))) !== null;
        });
    }

    protected static function findFavorite($id, $type, $returnQuery = false)
    {
        $query = static::query();
        $query->select('id');
        $thingId = static::findThingId($id, $type);
        $query->where([
            'thing_id' => $thingId,
            'thing_type' => $type,
            'user_id' => static::getCurrentUser()->id,
        ]);
        // Validation result will be false if any rows match the combination
        return $returnQuery ? $query : $query->first();
    }

    protected static function findThingId($id, $modelName = null)
    {
        $idParts = explode('/', $id);
        if (count($idParts) == 1) {
            $id = $idParts[0];
        } else {
            $id = array_pop($idParts);
            $modelName = $idParts[0];
        }
        if (!$modelName) {
            return false;
        }

        $class = static::getThingClass($modelName);

        if (!class_exists($class)) {
            return false;
        }

        return \Nitm\Content\Classes\ModelHelper::resolveId($modelName, $id, __NAMESPACE__);
    }

    public function beforeSave()
    {
        $attributes = [];
        $idParts = explode('/', $this->attributes['thing']);
        $thingType = array_get(post(), 'type', array_shift($idParts));
        $id = $this->resolveId($thingType, array_pop($idParts));
        if (!$id) {
            return false;
        }
        $existing = self::where([
            'thing_id' => $id,
            'thing_type' => $thingType,
            'user_id' => $this->getCurrentUser()->id,
        ])->withTrashed()->first();
        if ($existing) {
            $attributes['thing_type'] = $existing->thing_type;
            $attributes['thing_id'] = $existing->thing_id;
            $attributes['user_id'] = $existing->user_id;
            $attributes['deleted_at'] = null;
            $model->id = $existing->id;
            $model->exists = true;
        } else {
            $attributes['user_id'] = $this->getCurrentUser()->id;
            $attributes['thing_id'] = $id;
            $attributes['thing_type'] = $thingType;
        }
        $this->fill($attributes);
        unset($this->attributes['thing']);

        return $this->validate();
    }

    public function getThingAttribute()
    {
        $thingType = $this->thing_type;
        if ($this->thing_id && $this->relationLoaded($thingType)) {
            return $this->{$thingType};
        } else {
            return null;
        }
    }

    public static function getThingClass($modelName)
    {
        $class = '\Nitm\Content\Models\\Related' . studly_case($modelName);

        if (!class_exists($class)) {
            return false;
        }

        return $class;
    }

    public function getThingTitleAttribute()
    {
        return $this->thing ? $this->thing->title() : $this->thingId;
    }

    public function getThingPublicIdAttribute()
    {
        return $this->thing ? $this->thing->publicId : null;
    }

    public function getIsActiveAttribute()
    {
        return $this->deleted_at == null;
    }

    public function relationsToArray()
    {
        $result = parent::relationsToArray();
        $result['thing'] = array_get($result, $this->thing_type, []);
        $result['user'] = $this->user ? $this->user->toArray() : [];
        $result['user']['id'] = $result['user']['username'];
        $result['type'] = $this->thing_type;

        return array_only($result, ['thing', 'id', 'type', 'user']);
    }

    /**
     * Custom Find a model by custom keys.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public static function apiFind($id, $columns = ['*'])
    {
        if (!is_numeric($id)) {
            $idParts = explode('-', $id);
            $type = array_shift($idParts);

            return static::findFavorite($id, $type);
        } else {
            return parent::apiFind($id);
        }
    }

    /**
     * Custom Find a model by custom keys.
     *
     * @param array $params
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public static function apiQuery($params = [], $multiple = false, $query = null)
    {
        $id = array_pull($params, 'id');
        $query = static::query()->filterByUser($id);

        parent::apiQuery([], $multiple, $query);

        return $query;
    }

    public function scopeFilterByUser($query, $userId)
    {
        $currentUser = static::getCurrentUser();
        $currentUserId = $currentUser ? $currentUser->id : -1;
        if (is_string($userId)) {
            $user = User::apiFind($userId);
            if ($user) {
                $userId = $user->id;
            } else {
                $userId = $currentUserId;
            }
        } else {
            $userId = $currentUserId;
        }
        if ($currentUserId != $userId) {
            $userId = -1;
        }

        $query->where(['user_id' => $userId]);

        return $query;
    }
}

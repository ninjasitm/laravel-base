<?php

namespace Nitm\Content\Models;

use Model;
use DB;
use Validator;

/**
 * Model.
 */
class Rating extends BaseAction
{
    /*
      * Validation
      */
    public $rules = [
        'rater_id' => 'exists:users,id',
        'thing_id' => 'thing_exists',
        'rater' => 'unique_rating:nitm_ratings,rater,thing',
        'thing' => 'no_self_rating',
        'value' => 'required|integer|digits_between:1,5',
    ];

    public $customMessages = [
        'unique_rating' => 'Already rated!',
        'no_self_rating' => 'No self ratings!',
        'thing_exists' => "You're trying to rate something that doesn't exist!",
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_ratings';

    public $casts = [
        'thing' => 'array',
        'rater' => 'array',
    ];

    public $fillable = [
        'thing', 'rater', 'value', 'rater_id', 'thing_id', 'thing_class', 'thing_type', 'is_admin_action', 'created_at', 'updated_at', 'deleted_at', 'rater_username', 'rater_name', 'exists',
    ];

    public $visible = [
        'id', 'value', 'created_at', 'count',
    ];

    public $rater = ['rater'];
    public $with = ['rater'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public $belongsTo = [
        'rater' => [
            'Nitm\Content\Models\RelatedUser',
            'key' => 'rater_id',
            'remoteKey' => 'id',
        ],
    ];

    //For dyanmically attached relations
    public $dynamicContentConfig = [
        'key' => 'thing_id',
    ];

    protected static function setupCustomValidators()
    {
        Validator::extend('no_self_rating', function ($attribute, $value, $parameters, $validator) {
            $data = array_map(function ($value) {
                return preg_replace('/[^\da-z]/i', '', $value);
            }, $validator->getData());

            $rater = static::getCurrentUser();

            if (!$rater) {
                return false;
            }

            return $rater->id != $data['thing'];
        });

        Validator::extend('unique_rating', function ($attribute, $value, $parameters, $validator) {
            // Get table name from first parameter
            $table = array_shift($parameters);

            // Build the query
            $query = DB::table($table);
            $query->select('id');

            if (count(array_filter($validator->getData())) != count($parameters)) {
                return false;
            }

            // Add the field conditions
            foreach ($parameters as $i => $field) {
                $value = preg_replace('/[^\da-z]/i', '', $validator->getData()[$field]);
                $query->where($field . '_id', '=', $value);
            }

            // Validation result will be false if any rows match the combination
            return $query->count() == 0;
        });

        Validator::extend('thing_exists', function ($attribute, $value, $parameters, $validator) {
            // Get table name from first parameter
            $idParts = explode('/', array_get($validator->getData(), 'thing_id', array_get($validator->getData(), 'thing')));
            if (count($idParts) == 1) {
                $id = $idParts[0];
            } else {
                $id = array_pop($idParts);
                $modelName = array_pop($idPArts);
            }

            if (!isset($modelName)) {
                $modelName = array_get($validator->getData(), 'thing_type', array_get($validator->getData(), 'type'));
            }
            if (!$modelName) {
                return false;
            }

            $class = static::getThingClass($modelName);

            if (!class_exists($class)) {
                return false;
            }

            return $class::where(['id' => $id])->count() == 1;
        });
    }

    public static function getThingClass($modelName)
    {
        $class = '\Nitm\Content\Models\\' . studly_case($modelName);

        if (!class_exists($class)) {
            return false;
        }

        return $class;
    }

    public function getThing()
    {
        $thingType = array_get($this->attributes, 'thing_type');
        if ($thingType) {
            return $this->{$thingType};
        }
    }

    public static function recordRating(RatingObserver $observer)
    {
        $now = Carbon::now();
        $ratedOn = $now;
        $model = static::make([
            'rater_id' => $observer->getRater(),
            'thing_id' => $observer->getThing(),
            'is_admin_action' => $observer->getIsAdminAction(),
            'value' => $observer->getModel()->value,
            'created_at' => $ratedOn,
        ]);
        $model->save();
        $model->isRecorded = true;
        $observer->finish();
    }

    public function getRaterSummaryAttribute()
    {
        return $this->rater_username . '/' . $this->rater_id;
    }

    public function getThingSummaryAttribute()
    {
        return $this->thing_type . '/' . $this->thing_id;
    }

    public function getThingTitleAttribute()
    {
        return $this->getThing()->title();
    }

    public function beforeSave()
    {
        $idParts = explode('/', $this->thing);
        $id = array_pop($idParts);
        $thingType = array_get(post(), 'type', array_shift($idParts));

        $existing = self::where([
            'thing_id' => $id,
            'thing_type' => $thingType,
            'rater_id' => $this->getCurrentUser()->id,
        ])->first();
        if ($existing) {
            $attributes['value'] = $this->value;
            $attributes['thing_type'] = $existing->thing_type;
            $attributes['thing_id'] = $existing->thing_id;
            $this->id = $existing->id;
            $this->exists = true;
        } else {
            $attributes['rater_id'] = $this->getCurrentUser()->id;
            $attributes['rater_username'] = $this->getCurrentUser()->username;
            $attributes['rater_name'] = $this->getCurrentUser()->displayName;
            $attributes['thing_id'] = $id;
            $attributes['thing_type'] = $thingType;
            $attributes['thing_class'] = $this->getThingClass($thingType);
            $attributes['value'] = $this->value;
        }

        return $this->validate();
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
        if (is_numeric($id)) {
            return static::query()->where('rater_id', '=', $id)->select($columns)->get();
        } else {
            return static::query()->whereIn('rater_id', function ($query) use ($id) {
                $query->select('id')->from((new User())->getTable())->where('username', '=', $id);
            })->select($columns)->get();
        }
    }

    public function scopeUserRaterLimit($query)
    {
        return $query->take(10);
    }

    /**
     * {@inheritdoc}
     */
    public function newEloquentBuilder($query)
    {
        return new \Nitm\Content\Eloquent\Builder($query);
    }
}

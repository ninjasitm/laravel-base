<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\Activity;
use Nitm\Content\Models\Rating;

class RatingObserver extends BaseObserver
{
    /**TODO Add extra validation for creating follow models as current exists models don't work ***/
    public function saving($model)
    {
        $attributes = [];
        $idParts = explode('/', $model->thing);
        $id = array_pop($idParts);
        $thingType = array_get(post(), 'type', array_shift($idParts));
        unset($model->thing, $model->rater);
        $user = $this->formatActor(static::getUser());
        $existing = Rating::where([
           'thing_id' => $id,
           'thing_type' => $thingType,
           'rater_id' => $user['_model']['id'],
        ])->first();
        if ($existing) {
            $attributes['value'] = $model->value;
            $attributes['updated_at'] = \Carbon\Carbon::now();
            $attributes['is_admin_action'] = $this->getIsAdminAction();
            $attributes['thing_type'] = $existing->thing_type;
            $attributes['thing_id'] = $existing->thing_id;
            $model->id = $existing->id;
            $model->exists = true;
        } else {
            $attributes['rater_id'] = array_get($user, '_model.id');
            $attributes['rater_username'] = array_get($user, 'name');
            $attributes['rater_name'] = array_get($user, 'displayName');
            $attributes['thing_id'] = $id;
            $attributes['thing_type'] = $thingType;
            $attributes['thing_class'] = $model->getThingClass($thingType);
            $attributes['is_admin_action'] = $this->getIsAdminAction();
            $attributes['created_at'] = \Carbon\Carbon::now();
            $attributes['value'] = $model->value;
        }
        $model->fill($attributes);

        return $model->validate();
    }

    public function deleting($model)
    {
        $attributes = [];
        $attributes['deleted_at'] = \Carbon\Carbon::now();
        $model->attributes = $attributes;

        return true;
    }

    public function created($model)
    {
        $this->setupActivity('rated', $model, null, $model->getThing());
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function updated($model)
    {
        $this->setupActivity('rated', $model, null, $model->getThing());
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function deleted($model)
    {
        $this->setupActivity('rating-deleted', $model, null, $model->getThing());
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }
}

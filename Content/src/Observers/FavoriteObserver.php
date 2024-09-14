<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\Activity;
use Nitm\Content\Models\Favorite;

class FavoriteObserver extends BaseObserver
{
    /**TODO Add extra validation for creating follow models as current exists models don't work ***/
    public function saving($model)
    {
        $attributes = [];
        $idParts = explode('/', $model->attributes['thing']);
        $thingType = array_get(post(), 'type', array_shift($idParts));
        $id = $this->resolveId($thingType, array_pop($idParts));
        if (!$id) {
            return false;
        }
        $user = $this->formatActor(static::getUser(), true);
        $existing = Favorite::where([
           'thing_id' => $id,
           'thing_type' => $thingType,
           'user_id' => $user['_model']['id'],
        ])->withTrashed()->first();
        if ($existing) {
            $attributes['thing_type'] = $existing->thing_type;
            $attributes['thing_id'] = $existing->thing_id;
            $attributes['user_id'] = $existing->user_id;
            $attributes['deleted_at'] = null;
            $model->id = $existing->id;
            $model->exists = true;
        } else {
            $attributes['user_id'] = array_get($user, '_model.id');
            $attributes['thing_id'] = $id;
            $attributes['thing_type'] = $thingType;
        }
        $model->fill($attributes);
        unset($model->attributes['thing']);

        return $model->validate();
    }

    public function created($model)
    {
        $this->setupActivity('favorited', $model, null, $model->thing);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function updated($model)
    {
        $this->setupActivity('favorited', $model, null, $model->thing);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function deleted($model)
    {
        $this->setupActivity('unfavorited', $model, null, $model->thing);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }
}

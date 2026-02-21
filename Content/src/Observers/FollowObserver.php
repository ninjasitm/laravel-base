<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\User;
use Nitm\Content\Models\Activity;
use Nitm\Content\Models\Follow;

class FollowObserver extends BaseObserver
{
    /**TODO Add extra validation for creating follow models as current exists models don't work ***/
    public function saving($model)
    {
        $user = User::apiFind($model->followee);
        $this->setupActivity('follow', $user);
        $attributes = [];
        $currentUser = $this->getActor();
        $existing = Follow::where([
           'follower_id' => (int) $model->currentUser->id,
           'followee_id' => $user ? (int) $user->id : null,
           'type' => 'unfollow',
        ])->withTrashed()->first();
        if ($existing) {
            $attributes = [
               'type' => 'follow',
               'deleted_at' => null,
               'end_date' => null,
               'is_admin_action' => $this->getIsAdminAction(),
            ];
            $model->id = $existing->id;
            $model->exists = true;
            unset($model->rules['follower'], $model->rules['followee']);
        } else {
            $attributes = [
              'follower_id' => (int) $model->currentUser->id,
              'followee_id' => $user ? (int) $user->id : null,
              'follower' => $currentUser,
              'followee' => $this->formatUser($user),
              'title' => $this->getTitleString(),
              'is_admin_action' => $this->getIsAdminAction(),
              'type' => 'follow',
              'end_date' => null,
           ];
        }
        $attributes['start_date'] = \Carbon\Carbon::now();
        $model->fill($attributes);
        $model->rules['followee_id'] = 'required|'.$model->rules['followee_id'];
        $model->rules['follower_id'] = 'required|'.$model->rules['follower_id'];

        return $model->validate();
    }

    public function deleting($model)
    {
        $attributes = [];
        $attributes['type'] = 'unfollow';
        $attributes['end_date'] = \Carbon\Carbon::now();
        $attributes['start_date'] = null;
        $model->fill($attributes);

        return true;
    }

    public function created($model)
    {
        $this->setupActivity('follow', $model->followee, $model->follower);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function updated($model)
    {
        $action = $model->start_date ? 'unfollow' : 'follow';
        $this->setupActivity($action, $model->followee, $model->follower);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }

    public function deleted($model)
    {
        $this->setupActivity('unfollow', $model->followee, $model->follower);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }
}

<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\Activity;
use Nitm\Content\Models\EventAttendee;
use Nitm\Content\Models\Event;

class EventAttendeeObserver extends BaseObserver
{
    /**TODO Add extra validation for creating follow models as current exists models don't work ***/
    public function saving($model)
    {
        $this->setupActivity('follow', Event::find($model->event_id));
        $attributes = [];
        $currentUser = $this->getActor();
        $existing = EventAttendee::where([
           'attendee_id' => (int) $model->currentUser->id,
           'event_id' => $model->event_id,
        ])->first();
        if ($existing) {
            $attributes = [
               'type' => $model->status,
            ];
            $model->id = $existing->id;
            $model->exists = true;
            unset($model->rules['status']);
        } else {
            $attributes = [
              'attendee_id' => (int) $model->currentUser->id,
              'event_id' => $model->event_id,
              'status' => $model->status,
           ];
        }
        $model->fill($attributes);

        return $model->validate();
    }
    public function created($model)
    {
        $this->setupActivity($model->status, $model);
        Activity::recordActivity($this);
    }

    public function updated($model)
    {
        $this->setupActivity($model->status, $model);
        Activity::recordActivity($this);
    }

    public function deleted($model)
    {
        $this->setupActivity('not-going', $model);
        Activity::recordActivity($this);
    }
}

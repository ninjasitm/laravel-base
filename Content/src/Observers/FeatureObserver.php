<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\Activity;

class FeatureObserver extends BaseObserver
{
    public function created($model)
    {
        $this->setupActivity('feature', $model, null, $model->remote);
        Activity::recordActivity($this);
        $model->isPrepared = false;
    }
}

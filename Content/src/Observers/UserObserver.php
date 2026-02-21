<?php

namespace Nitm\Content\Observers;

class UserObserver extends BaseObserver
{
    public function created($model)
    {
        $this->setupActivity('join', $model, $model)->recordActivity();
    }

    public function formatTitle()
    {
        $objectName = array_get($this->object, 'displayName');
        if (!$objectName) {
            $objectName = array_get($this->object, 'name');
        }
        if ($this->getActionName() == 'joined') {
            return implode(' ', [$objectName, $this->getActionName(), \Config::get('app.name')]);
        } else {
            return parent::formatTitle();
        }
    }
}

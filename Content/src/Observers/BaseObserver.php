<?php

namespace Nitm\Content\Observers;

use Nitm\Content\Models\BaseContent;
use Nitm\Content\Classes\ImageHelper;

abstract class BaseObserver
{
    protected $title;

    /**
     * The model being observed.
     *
     * @var \Nitm\BaseContent model
     */
    protected $model;

    /**
     * The action that is currently being observed.
     *
     * @var [type]
     */
    protected $action;

    /**
     * The object that is acting.
     *
     * @var [type]
     */
    protected $object;

    /**
     * The object that is being acted upon.
     *
     * @var [type]
     */
    protected $target;

    /**
     * The actor that this activity occurred under.
     *
     * @var [type]
     */
    protected $actor;

    protected $_isAdminAction;

    protected static function getUser()
    {
        return auth()->user();
    }

    public function created($model)
    {
        $this->setupActivity('create', $model)->recordActivity();
    }

    public function updated($model)
    {
        $this->setupActivity('update', $model)->recordActivity();
    }

    public function deleted($model)
    {
        $this->setupActivity('delete', $model)->recordActivity();
    }

    protected function setupActivity($action, $object, $actor = null, $target = null)
    {
        $this->action = $action;
        $this->model = $object;
        $this->actor = $this->formatActor($actor ?: static::getUser());
        $this->object = $this->formatObject($object);
        if ($target) {
            $this->target = $this->formatTarget($target);
        }

        return $this;
    }

    public function finish()
    {
        $this->model = $this->action = $this->target = $this->object = null;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function resolveId($type, $id)
    {
        return \Nitm\Helpers\ModelHelper::resolveId($type, $id, '\\Nitm\\Content\\Models', ['withDeleted' => true]);
    }

    public function getActionName()
    {
        $action = $this->action;
        $endsWith = substr($action, strlen($action) - 1);
        if (in_array($endsWith, ['e'])) {
            return $action . 'd';
        } elseif (in_array($endsWith, ['y'])) {
            return substr($action, 0, strlen($action) - 1) . 'ied';
        } elseif (in_array($endsWith, ['d'])) {
            return $action;
        } else {
            return $action . 'ed';
        }
    }

    protected function getActionString()
    {
        switch ($this->getActionName()) {
            case 'created':
                return $this->getActionName() . ' new';
                break;

            default:
                return $this->getActionName();
                break;
        }
    }

    public function getTitleString()
    {
        return $this->formatTitle();
    }

    public function formatTitle()
    {
        $objectType = array_get($this->object, 'type');

        if ($objectType == 'user') {
            $objectName = '@' . array_get($this->object, 'name');
        } else {
            $objectName = array_get($this->object, 'displayName');
            if (!$objectName) {
                $objectName = array_get($this->object, 'name');
            }
            $objectName = '#' . $objectName;
        }

        $actorName = array_get($this->actor, 'name', array_get($this->actor, 'id'));
        if ($this->actor['type'] == 'user') {
            $actorName = '@' . $actorName;
        } else {
            $actorName = '#' . $actorName;
        }

        $parts = [
            $actorName,
            $this->getActionString(),
        ];

        if ($objectType != array_get($this->actor, 'type')) {
            array_push($parts, $objectType, $objectName);
        } else {
            array_push($parts, $objectName);
        }

        return implode(' ', $parts);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getActor()
    {
        return $this->actor;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getTarget()
    {
        return $this->target;
    }

    protected function recordActivity()
    {
        $ap = \App::make('Nitm\ActivityProvider');

        $result = $ap->recordActivity($this);
        $this->finish();

        return $result;
    }

    /**
     * Format the activity object.
     *
     * @param BaseContent $model The target
     * @param bool        $force Should we force formatting?
     *
     * @return array the formatted activity object
     */
    public function formatObject($model, $force = false)
    {
        if (!isset($this->object) || $force) {
            $this->object = $this->formatObjectOrTarget($model);
        }

        return $this->object;
    }

    /**
     * Format the activity target.
     *
     * @param BaseContent $model The target
     * @param bool        $force Should we force formatting?
     *
     * @return array the formatted activity target
     */
    protected function formatTarget($model, $force = false)
    {
        if (!isset($this->target) || $force) {
            $this->target = $this->formatObjectOrTarget($model);
        }

        return $this->target;
    }

    /**
     * Format the activity object.
     *
     * @param BaseContent $model The object
     *
     * @return array the formatted activity object
     */
    protected function formatObjectOrTarget($model)
    {
        if ($model instanceof \Rainlab\User\Models\User) {
            return $this->formatUser($model);
        } else {
            if (is_array($model)) {
                return $model;
            } elseif (is_object($model)) {
                return [
                    'id' => $model->publicId,
                    'type' => $model->is,
                    'url' => $model->publicId,
                    'name' => $model->title(),
                    'image' => [
                        'type' => 'link',
                        'url' => $model->image ? ImageHelper::createOrGetThumbnail($model->image, 256, 256) : '',
                    ],
                    '_model' => [
                        'id' => $model->id,
                        'class' => get_class($model),
                    ],
                ];
            }
        }
    }

    /**
     * Format the actor User object.
     *
     * @param User $model The actor
     * @param bool $force Should we force formatting?
     *
     * @return array the formatted actor object
     */
    protected function formatActor($model, $force = false)
    {
        $this->setIsAdminAction($model);
        if (!isset($this->actor) || $force) {
            $this->actor = $this->formatUser($model);
        }

        return $this->actor;
    }

    /**
     * Format the user User object.
     *
     * @param User $model The actor
     * @param bool $force Should we force formatting?
     *
     * @return array the formatted actor object
     */
    protected function formatUser($model)
    {
        if (is_array($model)) {
            return $model;
        } elseif (is_object($model)) {
            $id = $model->login ?: $model->username;

            return [
                'id' => 'user/' . $id,
                'type' => 'user',
                'url' => '/user/' . $id,
                'image' => [
                    'type' => 'link',
                    'url' => $model->avatar ? $model->avatar->getPath() : '',
                ],
                'name' => $id,
                'displayName' => $model->displayName,
                '_model' => [
                    'id' => $model->id,
                    'class' => get_class($model),
                ],
            ];
        }
    }

    public function getIsAdminAction()
    {
        return $this->_isAdminAction;
    }

    protected function setIsAdminAction()
    {
        $this->_isAdminAction = static::getUser() instanceof \Backend\Models\User;
    }
}
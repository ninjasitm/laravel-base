<?php

namespace Nitm\Content\Behaviors;

use October\Rain\Auth\Manager;

class Blamable extends \October\Rain\Extension\ExtensionBase
{
    public $owner;
    protected static $currentUser;

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;
        $owner->bindEvent('model.beforeCreate', function () use ($owner) {
            $owner->updateBlamable();
        });
        $owner->bindEvent('model.beforeUpdate', function () use ($owner) {
            $owner->updateBlamable(true);
        });
    }

    public function updateBlamable($updating = false)
    {
        if ($this->owner && property_exists($this->owner, 'blamable')) {
            $blamable = array_get($this->owner->blamable, $updating ? 'update' : 'create');
            if ($blamable) {
                $this->blame($blamable);
            }
        }
    }

    protected function blame($blamable)
    {
        if (static::getCurrentUser()) {
            foreach ((array) $blamable as $attribute) {
                $this->owner->attributes[$attribute] = static::getCurrentUser()->id;
            }
        }
    }

    public static function getCurrentUser($throwException = false)
    {
        if (!isset(static::$currentUser)) {
            if (\App::runningInBackend()) {
                $user = \BackendAuth::getUser();
                if ($user) {
                    $relatedUser = \Rainlab\User\Models\User::query()
                   ->where([
                      'email' => $user->email,
                   ])->first();
                    static::$currentUser = $relatedUser ?: $user;
                }
            } else {
                $user = \Auth::getUser();
                if (!$user) {
                    $user = Manager::instance()->getUser();
                }
                static::$currentUser = $user;
            }
        }

        if (\App::environment() == 'testing' && !static::$currentUser) {
            static::$currentUser = new \Nitm\Content\Models\RelatedUser([
               'id' => 1,
               'name' => 'Test User',
               'username' => 'testuser',
               'password' => 'testpassword',
               'password_confirmation' => 'testpassword',
            ]);
        }
        if (!static::$currentUser && $throwException) {
            throw new \Exception("You can't do that. Do you need to login first?", 403);
        }

        return static::$currentUser;
    }

    public function getCurrentUserAttribute()
    {
        return static::getCurrentUser();
    }
}
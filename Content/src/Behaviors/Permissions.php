<?php

namespace Nitm\Content\Behaviors;

use October\Rain\Auth\Manager;

class Permissions extends \October\Rain\Extension\ExtensionBase
{
    public $owner;
    protected static $currentUser;

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        try {
            if ($owner instanceof \Nitm\Content\Models\User) {
                $this->owner = $owner;
            } else {
                if ($owner->relationLoaded('author')) {
                    $this->owner = $owner->author;
                } elseif ($owner->relationLoaded('user')) {
                    $this->owner = $owner->user;
                } else {
                    $this->owner = $owner;
                }
            }
        } catch (\Exception $e) {
            $this->owner = null;
        }
    }

     /**
      * Permission checking.
      *
      * @param string $action The action being performed
      * @param User   $user   The user
      *
      * @return [type] [description]
      */
     public function can($action)
     {
         if (method_exists($this, 'can'.$action)) {
             return $this->{'can'.$action}();
         }
     }

     /**
      * Permission checking for whether the user can edit the.
      *
      * @param User $user The user
      *
      * @return [type] [description]
      */
     protected function canUpdate()
     {
         $user = static::getCurrentUser();
         if (!$this->owner || !$user) {
             return false;
         }

         return $user && !$user->isBanned() && $this->isOwner();
     }

     /**
      * Permission checking for whether the user can create content.
      *
      * @return [type] [description]
      */
     protected function canCreate()
     {
         return static::getCurrentUser() != null;
     }

     /**
      * Permission checking for whether the user can edit the.
      *
      * @param User $user The user
      *
      * @return [type] [description]
      */
     protected function canDelete()
     {
         $user = static::getCurrentUser();
         if (!$this->owner || !$user) {
             return false;
         }

         return $user && !$user->isBanned() && $this->isOwner();
     }

     /**
      * Permission checking for whether the user can read the specified resource.
      *
      * @param User $user The user
      *
      * @return [type] [description]
      */
     protected function canRead()
     {
         return true;
     }

     /**
      * Permission checking for whether the user can read.
      *
      * @param User $user The user
      *
      * @return [type] [description]
      */
     protected function canReadAll()
     {
         return true;
     }

     /**
      * Is the specified user the owner?
      *
      * @param [type] $user [description]
      *
      * @return bool [description]
      */
     protected function isOwner()
     {
         $ownerId = null;
         if ($this->owner instanceof \Nitm\Content\Models\Post
            || $this->owner instanceof \Nitm\Content\Models\Favorite) {
             $ownerId = $this->owner->user_id;
         } elseif ($this->owner instanceof \Nitm\Content\Models\Follow) {
             $ownerId = $this->owner->follwer_id;
         } elseif ($this->owner instanceof \Nitm\Content\Models\Rating) {
             $ownerId = $this->owner->rater_id;
         } elseif ($this->owner instanceof \Nitm\Content\Models\Art
            || $this->owner instanceof \Nitm\Content\Models\Event) {
             $ownerId = $this->owner->author_id;
         } elseif ($this->owner instanceof \RainLab\User\Models\User) {
             $ownerId = $this->owner->id;
         }

         return static::getCurrentUser() && static::getCurrentUser()->id == $ownerId;
     }

    public static function getCurrentUser($throwException = false)
    {
        if (!isset(static::$currentUser)) {
            if (\App::runningInBackend()) {
                $user = \BackendAuth::getUser();
                $relatedUser = \Rainlab\User\Models\User::query()
                ->where([
                   'email' => $user->email,
                ])->first();
                static::$currentUser = $relatedUser ?: $user;
            } else {
                $user = \Auth::getUser();
                if (!$user) {
                    $user = Manager::instance()->getUser();
                }
                static::$currentUser = $user;
            }
        }

        if (\App::environment() == 'testing' && !static::$currentUser) {
            static::$currentUser = new \Nitm\Content\Models\User([
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

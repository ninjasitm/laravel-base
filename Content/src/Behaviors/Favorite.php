<?php

namespace Nitm\Content\Behaviors;

use Nitm\Content\Models\RelatedFavorite as FavoriteModel;

class Favorite extends \October\Rain\Extension\ExtensionBase
{
    public $owner;

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;

        $currentUserId = Blamable::getCurrentUser() ? Blamable::getCurrentUser()->id : -1;
        $this->owner->hasOne = array_merge($this->owner->hasOne, [
           'favoriteCount' => [
             FavoriteModel::class,
             'key' => 'thing_id',
             'otherKey' => 'id',
             'count' => true,
           ],
           'currentUserFavorite' => [
             FavoriteModel::class,
             'key' => 'thing_id',
             'otherKey' => 'id',
             'conditions' => 'user_id='.$currentUserId.' AND deleted_at IS NULL',
           ],
       ]);

        $this->owner->hasMany = array_merge($this->owner->hasMany, [
          'favorites' => [
               FavoriteModel::class,
               'key' => 'thing_id',
               'otherKey' => 'id',
               'condition' => 'deleted_at IS NULL',
               'order' => 'id desc',
            ],
         ]);

        $relations = ['favoriteCount', 'currentUserFavorite'];
        $ownerClass = get_class($this->owner);
        $this->owner->appendWith($relations);
        $this->owner->appendVisible(array_merge($relations, ['favorites']));
        if ($this->owner instanceof \Rainlab\User\Models\User) {
            $this->owner->appendEagerWith(['favorites']);
        }
    }
}

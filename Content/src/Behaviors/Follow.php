<?php

namespace Nitm\Content\Behaviors;

use Nitm\Content\Models\RelatedFollow as FollowModel;
use Nitm\Content\Classes\QueryHelper;

class Follow extends \October\Rain\Extension\ExtensionBase
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
           'followerCount' => [
               FollowModel::class,
               'key' => 'followee_id',
               'otherKey' => 'id',
               'count' => true,
               'conditions' => QueryHelper::searchJsonField([
                   'follower' => [
                       'type' => $this->owner->is
                   ]
               ])
           ],
            'followingCount' => [
               FollowModel::class,
               'key' => 'follower_id',
               'otherKey' => 'id',
               'count' => true,
               'conditions' => QueryHelper::searchJsonField([
                   'followee' => [
                       'type' => $this->owner->is
                   ]
               ]),
            ],
             'currentUserFollow' => [
               FollowModel::class,
               'key' => 'followee_id',
               'otherKey' => 'id',
               'conditions' => 'follower_id='.$currentUserId.' AND deleted_at IS NULL',
             ],
         ]);

        $this->owner->hasMany = array_merge($this->owner->hasMany, [
             'followers' => [
               FollowModel::class,
               'key' => 'followee_id',
               'otherKey' => 'id',
               'scope' => 'followLimit',
               'conditions' => QueryHelper::searchJsonField([
                   'follower' => [
                       'type' => $this->owner->is
                   ]
               ])." AND deleted_at IS NULL",
               'order' => 'id desc',
             ],
            'following' => [
               FollowModel::class,
               'key' => 'follower_id',
               'otherKey' => 'id',
               'scope' => 'followLimit',
               'conditions' => QueryHelper::searchJsonField([
                   'followee' => [
                       'type' => $this->owner->is
                   ]
               ])." AND deleted_at IS NULL",
               'order' => 'id desc',
            ],
        ]);

        $relations = ['followerCount', 'followingCount', 'currentUserFollow'];
        $this->owner->appendVisible(array_merge($relations, ['followers', 'following']));
        $this->owner->appendWith($relations);
        $this->owner->appendEagerWith(['followers', 'following']);
    }
}

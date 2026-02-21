<?php

namespace Nitm\Content\Behaviors;

use Nitm\Content\Models\RelatedRating as RatingModel;

class Rating extends \October\Rain\Extension\ExtensionBase
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
           'rating' => [
              RatingModel::class,
              'key' => 'thing_id',
              'otherKey' => 'id',
              'conditions' => "thing_type='".$this->owner->is."'",
              'select' => 'AVG(value) as value',
           ],
             'currentUserRating' => [
               RatingModel::class,
               'key' => 'thing_id',
              'otherKey' => 'id',
              'conditions' => 'rater_id='.$currentUserId.' AND deleted_at IS NULL',
             ],
         ]);

        $this->owner->hasMany['ratings'] = [
           RatingModel::class,
           'key' => 'id',
           'otherKey' => 'thing_id',
           'conditions' => "thing_type='".$this->owner->is."'",
           'order' => 'id desc',
        ];

        $relations = ['rating', 'currentUserRating'];
        $this->owner->appendVisible(array_merge($relations, ['ratings']));
        $this->owner->appendWith($relations);
        $this->owner->appendEagerWith(['ratings']);
    }
}

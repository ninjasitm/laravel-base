<?php

namespace Nitm\Content\Models;

use Model;
use RainLab\Blog\Models\Category as BaseCategory;

/**
 * Model.
 */
class PostCategory extends BaseCategory
{
    use \Nitm\Content\Traits\Model;

    public $implement = ['@Nitm.Content.Behaviors.Search', '@Nitm.Content.Behaviors.Permissions'];

    public $visible = [
      'name', 'slug', 'description',
   ];

    public $with = [
   ];

    public $eagerWith = [
   ];

    public $belongsToMany = [
      'posts' => [
           'Nitm\Content\Models\Post',
            'table' => 'rainlab_blog_posts_categories',
            'order' => 'published_at desc',
            'scope' => 'isPublished',
      ],
   ];

    public function getMorphClass()
    {
        return 'RainLab\Post\Models\Category';
    }

    public static function apiFind($id, $options = [])
    {
        return static::internalApiFind($id, array_merge([
           'columns' => '*',
           'stringColumns' => ['slug'],
        ], $options));
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes['id'] = $attributes['slug'];

        return array_reverse($attributes);
    }

    public function toFeedArray()
    {
        $attributes = parent::toArray();

        return $attributes;
    }
}

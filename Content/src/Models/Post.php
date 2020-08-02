<?php

namespace Nitm\Content\Models;

use Model;
use RainLab\Blog\Models\Post as BasePost;
use Nitm\Content\Classes\ImageHelper;

/**
 * Model.
 */
class Post extends BasePost
{
    use \Nitm\Content\Traits\Feature;
    use \Nitm\Content\Traits\Model;

    public $implement = [
      '@Nitm.Content.Behaviors.Search',
      '@Nitm.Content.Behaviors.Permissions',
      '@Nitm.Content.Behaviors.Blamable',
      '@Nitm.Content.Behaviors.Rating',
      '@Nitm.Content.Behaviors.Follow',
      '@Nitm.Content.Behaviors.Favorite',
   ];

    public $visible = [
      'id', 'title', 'slug', 'excerpt', 'content', 'content_html', 'author',
      'date', 'updated',
      'featured_images', 'content_images', 'categories', 'image',
   ];

    public $appends = ['date', 'updated', 'image'];

    public $blamable = [
     'create' => 'user_id',
  ];

    public $fillable = [
     'title', 'slug', 'excerpt', 'content', 'featured_images', 'content_images', 'published',
  ];

    public $with = [
      'author', 'featured_images', 'categories',
   ];

    public $eagerWith = [
      'content_images',
   ];

    /*
     * Relations
     */
    public $belongsTo = [
      'author' => ['Nitm\Content\Models\RelatedUser', 'key' => 'user_id'],
      'user' => ['Nitm\Content\Models\RelatedUser'],
   ];

    public $belongsToMany = [
      'categories' => [
           'Nitm\Content\Models\PostCategory',
           'table' => 'rainlab_blog_posts_categories',
           'otherKey' => 'category_id',
           'key' => 'post_id',
           'order' => 'name',
      ],
   ];

    public $attachMany = [
      'featured_images' => ['Nitm\Content\Models\File', 'order' => 'sort_order'],
      'content_images' => ['Nitm\Content\Models\File'],
   ];

    public function getMorphClass()
    {
        return 'RainLab\Blog\Models\Post';
    }

    public function beforeCreate()
    {
        $this->updateBlamable();
    }

    public function beforeValidate()
    {
        //Need to prevent the ability for frontend users to set post category to Featured
        if (isset($this->attributes['published']) && $this->attributes['published']) {
            $this->attributes['published_at'] = \Carbon\Carbon::now();
        }
        if (!array_get($this->attributes, 'slug', false)) {
            $this->attributes['slug'] = \Auth::getUser()->username.'-'.str_slug($this->attributes['title']);
        }
        if (!array_get($this->attributes, 'excerpt', false)) {
            $this->attributes['excerpt'] = substr(strip_tags($this->attributes['content']), 0, 140);
        }
    }

    public static function apiFind($id, $options = [])
    {
        return static::internalApiFind($id, array_merge([
           'columns' => '*',
           'stringColumns' => ['slug'],
        ], $options));
    }

    public function toFeedArray()
    {
        $attributes = parent::toArray();
        $attributes['content'] = $attributes['excerpt'];
        $attributes['id'] = $this->publicId;

        return $attributes;
    }

    public function toArray($fullContent=false)
    {
        $attributes = parent::toArray();
        $genericAvatar = ImageHelper::getPlaceHolderAvatar();
        $attributes['author'] = [
            'name' => $this->user ? $this->user->name : 'NITM',
            'image' => $this->user ? ($this->user->avatar ? $this->user->avatar->getPath() : $genericAvatar) : $genericAvatar
        ];
        $attributes['isLocalImage'] = false;
        $attributes['image'] = $this->featured_images->count() ? $this->featured_images->first()->getPath() : ImageHelper::getPlaceHolderBackground();
        if ($fullContent) {
            $attributes['text'] = $this->content;
            $attributes['html'] = $this->content_html;
        }

        return $attributes;
    }

    /**
     * Get the image attribute.
     */
    public function getImageAttribute()
    {
        return array_get($this->featured_images, 0, []);
    }

    public function getDateAttribute()
    {
        return $this->published_at ? $this->published_at->toFormattedDateString() : 'Just Now';
    }
    public function getUpdatedAttribute()
    {
        return $this->updated_at ? $this->updated_at->toFormattedDateString() : '';
    }

    /**
     * Filter a related art metadata list.
     *
     * @param string  $type    The list type
     * @param Builder $query   The query being filtered
     * @param array   $options The items that make upthe filter
     */
    public function scopeFilterByCategory($query, $categories)
    {
        $categories = (array) $categories;
        $query->where(function ($query) use ($categories) {
            $class = PostCategory::class;
            $query->whereIn('id', function ($query) use ($categories) {
                $query->select('post_id')
                  ->from('rainlab_blog_posts_categories')
                  ->whereIn('category_id', function ($query) use ($categories) {
                      $query->select('id')
                        ->from('rainlab_blog_categories')
                        ->whereIn('slug', $categories);
                  });
            });
        });
    }
}

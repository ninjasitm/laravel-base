<?php

namespace Nitm\Content\Models;

use Nitm\Content\Models\BaseModel as Model;
use Nitm\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Nitm\Content\Traits\Sluggable;

/**
 * Class Post
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property \Nitm\Content\Models\User $user
 * @property integer $user_id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property string $content_html
 * @property string|\Carbon\Carbon $published_at
 * @property boolean $published
 */
class Post extends Model
{
    use SoftDeletes, Sluggable;

    public $table = 'posts';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'content_html',
        'published_at',
        'published'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'excerpt' => 'string',
        'content' => 'string',
        'content_html' => 'string',
        'published_at' => 'datetime',
        'published' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required'
    ];

    public $createdByAuthFields = [
        'user_id'
    ];

    protected $slugs = [
        'slug' => 'title',
    ];

    /**
     * @inheritDoc
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            //Need to prevent the ability for frontend users to set post category to Featured
            if ($model->getAttribute("published")) {
                $model->published_at = \Carbon\Carbon::now();
            }
            if (!Arr::get($model->getAttributes(), 'slug', false)) {
                $model->slug = \Auth::getUser()->username . '-' . str_slug($model->getAttribute("title"));
            }
            if (!Arr::get($model->attributes, 'excerpt', false)) {
                $model->excerpt = substr(strip_tags($model->getAttribute("content")), 0, 140);
            }
        });
    }

    public function toArray($fullContent = false)
    {
        $attributes = parent::toArray();
        $genericAvatar = ImageHelper::getPlaceHolderAvatar();
        $attributes['author'] = [
            'name' => $this->user ? $this->user->name : 'NITM',
            'image' => $this->user ? ($this->user->avatar ? $this->user->avatar->getPath() : $genericAvatar) : $genericAvatar
        ];
        $attributes['isLocalImage'] = false;
        $attributes['image'] = $this->images->count() ? $this->images->first()->getPath() : ImageHelper::getPlaceHolderBackground();
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
        return Arr::get($this->images, 0, []);
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
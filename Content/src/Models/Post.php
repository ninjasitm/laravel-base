<?php

namespace Nitm\Content\Models;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Nitm\Helpers\ImageHelper;
use Nitm\Content\Traits\Sluggable;
use Nitm\Content\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nitm\Content\Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Post
 *
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
        'published',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'           => 'integer',
        'user_id'      => 'integer',
        'title'        => 'string',
        'slug'         => 'string',
        'excerpt'      => 'string',
        'content'      => 'string',
        'content_html' => 'string',
        'published_at' => 'datetime',
        'published'    => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required',
    ];

    public $createdByAuthFields = [
        'user_id',
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

        static::saving(
            function ($model) {
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
            }
        );
    }

    /**
     * @param bool $fullContent
     *
     * @return array
     */
    public function toArray($fullContent = false): array
    {
        $attributes           = parent::toArray();
        $genericAvatar        = ImageHelper::getPlaceHolderAvatar();
        $attributes['author'] = [
            'name'  => $this->user ? $this->user->name : 'NITM',
            'image' => $this->user ? ($this->user->avatar ? $this->user->avatar->getPath() : $genericAvatar) : $genericAvatar,
        ];
        // $attributes['image'] = $this->images->count() ? $this->images->first()->getPath() : ImageHelper::getPlaceHolderBackground();
        if ($fullContent) {
            $attributes['text'] = $this->content;
            $attributes['html'] = $this->content_html;
        }

        return $attributes;
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'posts_categories', 'post_id', 'category_id');
    }

    /**
     * Get the image attribute.
     */
    // public function getImageAttribute()
    // {
    //     return Arr::get($this->images, 0, []);
    // }

    public function getDateAttribute()
    {
        return $this->published_at ? $this->published_at->toFormattedDateString() : 'Just Now';
    }
    public function getUpdatedAttribute()
    {
        return $this->updated_at ? $this->updated_at->toFormattedDateString() : '';
    }

    //
    // Scopes
    //

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<', Carbon::now());
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
        $query->where(
            function ($query) use ($categories) {
                $class = PostCategory::class;
                $query->whereIn(
                    'id',
                    function ($query) use ($categories) {
                        $query->select('post_id')
                            ->from('rainlab_blog_posts_categories')
                            ->whereIn(
                                'category_id',
                                function ($query) use ($categories) {
                                    $query->select('id')
                                        ->from('rainlab_blog_categories')
                                        ->whereIn('slug', $categories);
                                }
                            );
                    }
                );
            }
        );
    }
    /**
     * Allows filtering for specifc categories.
     *
     * @param  Illuminate\Query\Builder $query      QueryBuilder
     * @param  array                    $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas(
            'categories',
            function ($q) use ($categories) {
                $q->whereIn('id', $categories);
            }
        );
    }

    //
    // Summary / Excerpt
    //

    /**
     * Used by "has_summary", returns true if this post uses a summary (more tag).
     *
     * @return boolean
     */
    public function getHasSummaryAttribute()
    {
        $more = '<!-- more -->';

        return (!!strlen(trim($this->excerpt)) ||
            strpos($this->content_html, $more) !== false ||
            strlen(Html::strip($this->content_html)) > 600);
    }

    /**
     * Used by "summary", if no excerpt is provided, generate one from the content.
     * Returns the HTML content before the <!-- more --> tag or a limited 600
     * character version.
     *
     * @return string
     */
    public function getSummaryAttribute()
    {
        $excerpt = $this->excerpt;
        if (strlen(trim($excerpt))) {
            return $excerpt;
        }

        $more = '<!-- more -->';
        if (strpos($this->content_html, $more) !== false) {
            $parts = explode($more, $this->content_html);

            return array_get($parts, 0);
        }

        return Html::limit($this->content_html, 600);
    }

    //
    // Next / Previous
    //

    /**
     * Apply a constraint to the query to find the nearest sibling
     *
     *     // Get the next post
     *     Post::applySibling()->first();
     *
     *     // Get the previous post
     *     Post::applySibling(-1)->first();
     *
     *     // Get the previous post, ordered by the ID attribute instead
     *     Post::applySibling(['direction' => -1, 'attribute' => 'id'])->first();
     *
     * @param  $query
     * @param  array $options
     * @return
     */
    public function scopeApplySibling($query, $options = [])
    {
        if (!is_array($options)) {
            $options = ['direction' => $options];
        }

        extract(
            array_merge(
                [
                    'direction' => 'next',
                    'attribute' => 'published_at',
                ],
                $options
            )
        );

        $isPrevious        = in_array($direction, ['previous', -1]);
        $directionOrder    = $isPrevious ? 'asc' : 'desc';
        $directionOperator = $isPrevious ? '>' : '<';

        $query->where('id', '<>', $this->id);

        if (!is_null($this->$attribute)) {
            $query->where($attribute, $directionOperator, $this->$attribute);
        }

        return $query->orderBy($attribute, $directionOrder);
    }

    /**
     * Returns the next post, if available.
     *
     * @return self
     */
    public function nextPost()
    {
        return self::isPublished()->applySibling()->first();
    }

    /**
     * Returns the previous post, if available.
     *
     * @return self
     */
    public function previousPost()
    {
        return self::isPublished()->applySibling(-1)->first();
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return PostFactory::new();
    }
}